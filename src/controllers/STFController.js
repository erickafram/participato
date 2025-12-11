/**
 * Controller de Integração com STF
 * Busca dados de processos do Supremo Tribunal Federal
 */
const https = require('https');
const { Setting } = require('../models');

class STFController {
  /**
   * Buscar dados de um processo pelo número do incidente
   */
  async fetchProcess(req, res) {
    try {
      const { incidente } = req.query;
      
      if (!incidente) {
        return res.status(400).json({ 
          success: false, 
          message: 'Número do incidente é obrigatório.' 
        });
      }

      const processData = await this.scrapeSTFProcess(incidente);
      
      if (!processData) {
        return res.status(404).json({ 
          success: false, 
          message: 'Processo não encontrado ou erro ao acessar o STF.' 
        });
      }

      return res.json({ success: true, data: processData });
    } catch (error) {
      console.error('Erro ao buscar processo STF:', error);
      return res.status(500).json({ 
        success: false, 
        message: 'Erro ao buscar processo: ' + error.message 
      });
    }
  }

  /**
   * Fazer scraping do portal do STF
   */
  async scrapeSTFProcess(incidente) {
    return new Promise((resolve) => {
      const url = `https://portal.stf.jus.br/processos/detalhe.asp?incidente=${incidente}`;
      
      const options = {
        hostname: 'portal.stf.jus.br',
        path: `/processos/detalhe.asp?incidente=${incidente}`,
        method: 'GET',
        headers: {
          'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
          'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
          'Accept-Language': 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
          'Accept-Encoding': 'identity',
          'Connection': 'keep-alive'
        },
        timeout: 30000
      };

      const req = https.request(options, (res) => {
        let data = '';
        res.setEncoding('utf8');
        
        res.on('data', chunk => data += chunk);
        res.on('end', () => {
          try {
            const processInfo = this.parseSTFPage(data, incidente);
            resolve(processInfo);
          } catch (e) {
            console.error('Erro ao parsear página STF:', e);
            resolve(null);
          }
        });
      });

      req.on('error', (e) => {
        console.error('Erro na requisição STF:', e);
        resolve(null);
      });

      req.on('timeout', () => {
        req.destroy();
        resolve(null);
      });

      req.end();
    });
  }

  /**
   * Parsear HTML do STF e extrair informações
   */
  parseSTFPage(html, incidente) {
    const result = {
      incidente,
      numero: '',
      classe: '',
      relator: '',
      partes: [],
      assuntos: [],
      andamentos: [],
      decisoes: [],
      votos: [],
      situacao: '',
      dataProtocolo: '',
      url: `https://portal.stf.jus.br/processos/detalhe.asp?incidente=${incidente}`
    };

    // Extrair número do processo
    const numeroMatch = html.match(/processo-numero[^>]*>([^<]+)/i) ||
                        html.match(/<h2[^>]*class="[^"]*processo[^"]*"[^>]*>([^<]+)/i) ||
                        html.match(/ADI\s*\d+|ADPF\s*\d+|RE\s*\d+|HC\s*\d+|MS\s*\d+|Pet\s*\d+/i);
    if (numeroMatch) {
      result.numero = numeroMatch[1] ? numeroMatch[1].trim() : numeroMatch[0].trim();
    }

    // Extrair classe processual
    const classeMatch = html.match(/Classe[^:]*:\s*<[^>]*>([^<]+)/i) ||
                        html.match(/classe-processual[^>]*>([^<]+)/i);
    if (classeMatch) {
      result.classe = classeMatch[1].trim();
    }

    // Extrair relator
    const relatorMatch = html.match(/Relator[^:]*:\s*(?:<[^>]*>)*\s*(?:MIN\.|MINISTRO|MINISTRA)?\s*([^<\n]+)/i) ||
                         html.match(/relator[^>]*>(?:MIN\.|MINISTRO|MINISTRA)?\s*([^<]+)/i);
    if (relatorMatch) {
      result.relator = relatorMatch[1].trim().replace(/\s+/g, ' ');
    }

    // Extrair partes (requerente/requerido)
    const partesRegex = /(?:Requerente|Requerido|Autor|Réu|Impetrante|Impetrado|Reclamante|Reclamado)[^:]*:\s*(?:<[^>]*>)*([^<]+)/gi;
    let parteMatch;
    while ((parteMatch = partesRegex.exec(html)) !== null) {
      const parte = parteMatch[1].trim();
      if (parte && parte.length > 2 && !result.partes.includes(parte)) {
        result.partes.push(parte);
      }
    }

    // Extrair assuntos
    const assuntosMatch = html.match(/Assunto[^:]*:\s*(?:<[^>]*>)*([^<]+)/gi);
    if (assuntosMatch) {
      assuntosMatch.forEach(m => {
        const assunto = m.replace(/Assunto[^:]*:\s*/i, '').replace(/<[^>]*>/g, '').trim();
        if (assunto && assunto.length > 3) {
          result.assuntos.push(assunto);
        }
      });
    }

    // Extrair situação/status
    const situacaoMatch = html.match(/(?:Situação|Status)[^:]*:\s*(?:<[^>]*>)*([^<]+)/i);
    if (situacaoMatch) {
      result.situacao = situacaoMatch[1].trim();
    }

    // Extrair andamentos/movimentações
    const andamentoRegex = /<tr[^>]*>[\s\S]*?(\d{2}\/\d{2}\/\d{4})[\s\S]*?<td[^>]*>([\s\S]*?)<\/td>[\s\S]*?<\/tr>/gi;
    let andamentoMatch;
    let count = 0;
    while ((andamentoMatch = andamentoRegex.exec(html)) !== null && count < 20) {
      const data = andamentoMatch[1];
      const descricao = andamentoMatch[2].replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
      if (descricao && descricao.length > 5) {
        result.andamentos.push({ data, descricao });
        count++;
      }
    }

    // Tentar extrair votos (se houver placar de votação)
    const votosRegex = /(?:MIN\.|MINISTRO|MINISTRA)\s+([^<\n]+)[\s\S]*?(procedente|improcedente|provido|desprovido|deferido|indeferido|favorável|contrário)/gi;
    let votoMatch;
    while ((votoMatch = votosRegex.exec(html)) !== null) {
      result.votos.push({
        ministro: votoMatch[1].trim(),
        voto: votoMatch[2].trim()
      });
    }

    // Extrair decisões recentes
    const decisaoRegex = /(?:Decisão|Despacho|Acórdão)[^:]*:?\s*(?:<[^>]*>)*([^<]{20,500})/gi;
    let decisaoMatch;
    while ((decisaoMatch = decisaoRegex.exec(html)) !== null && result.decisoes.length < 5) {
      const decisao = decisaoMatch[1].trim();
      if (decisao && !result.decisoes.includes(decisao)) {
        result.decisoes.push(decisao);
      }
    }

    return result;
  }

  /**
   * Gerar matéria sobre processo usando IA
   */
  async generateArticle(req, res) {
    try {
      const { incidente, style = 'metropoles_politica' } = req.body;
      
      if (!incidente) {
        return res.status(400).json({ 
          success: false, 
          message: 'Número do incidente é obrigatório.' 
        });
      }

      // Buscar dados do processo
      const processData = await this.scrapeSTFProcess(incidente);
      
      if (!processData) {
        return res.status(404).json({ 
          success: false, 
          message: 'Não foi possível obter dados do processo.' 
        });
      }

      // Buscar configurações da IA
      const settings = await this.getAISettings();
      
      if (settings.ai_enabled !== 'true' && settings.ai_enabled !== '1') {
        return res.status(400).json({ 
          success: false, 
          message: 'Assistente de IA está desativado.' 
        });
      }

      if (!settings.ai_api_key) {
        return res.status(400).json({ 
          success: false, 
          message: 'Chave da API não configurada.' 
        });
      }

      // Montar contexto para a IA
      const context = this.buildContext(processData);
      
      // Gerar matéria
      const prompt = this.buildPrompt(context, style);
      const article = await this.callAI(prompt, settings, style);

      return res.json({ 
        success: true, 
        data: article,
        processData 
      });
    } catch (error) {
      console.error('Erro ao gerar matéria STF:', error);
      return res.status(500).json({ 
        success: false, 
        message: error.message 
      });
    }
  }

  /**
   * Construir contexto a partir dos dados do processo
   */
  buildContext(data) {
    let context = `DADOS DO PROCESSO NO STF:\n`;
    context += `Número: ${data.numero || 'N/D'}\n`;
    context += `Classe: ${data.classe || 'N/D'}\n`;
    context += `Relator: ${data.relator || 'N/D'}\n`;
    context += `Situação: ${data.situacao || 'N/D'}\n`;
    
    if (data.partes.length > 0) {
      context += `\nPARTES:\n${data.partes.join('\n')}\n`;
    }
    
    if (data.assuntos.length > 0) {
      context += `\nASSUNTOS:\n${data.assuntos.join('\n')}\n`;
    }
    
    if (data.votos.length > 0) {
      context += `\nVOTOS DOS MINISTROS:\n`;
      data.votos.forEach(v => {
        context += `- ${v.ministro}: ${v.voto}\n`;
      });
    }
    
    if (data.andamentos.length > 0) {
      context += `\nÚLTIMAS MOVIMENTAÇÕES:\n`;
      data.andamentos.slice(0, 10).forEach(a => {
        context += `[${a.data}] ${a.descricao}\n`;
      });
    }
    
    if (data.decisoes.length > 0) {
      context += `\nDECISÕES:\n`;
      data.decisoes.forEach(d => {
        context += `- ${d}\n`;
      });
    }

    return context;
  }

  /**
   * Construir prompt para a IA
   */
  buildPrompt(context, style) {
    const hoje = new Date().toLocaleDateString('pt-BR', { 
      day: 'numeric', 
      month: 'long', 
      year: 'numeric' 
    });

    return `DATA: ${hoje}

${context}

TAREFA: Escreva uma matéria jornalística sobre este processo no STF.

INSTRUÇÕES:
- Use APENAS as informações fornecidas acima
- NÃO invente fatos, nomes ou decisões
- Se faltar informação, indique com [aguardando atualização]
- Foque no que é relevante para o público
- Explique termos jurídicos de forma acessível
- Mínimo 6 parágrafos bem desenvolvidos

ESTRUTURA:
1. TÍTULO: Destaque o ponto principal (decisão, votação, andamento)
2. SUBTÍTULO: Contexto adicional importante
3. LIDE: O que aconteceu/está acontecendo no processo
4. DESENVOLVIMENTO: Detalhes, votos, argumentos
5. CONTEXTO: Importância do caso, implicações
6. PRÓXIMOS PASSOS: O que esperar (se houver informação)

Retorne em JSON:
{"title": "título", "subtitle": "subtítulo", "content": "HTML com <p> para parágrafos"}`;
  }

  /**
   * Chamar API da IA
   */
  async callAI(prompt, settings, style) {
    const AIController = require('./AIController');
    const response = await AIController.callTogetherAI(prompt, settings, style);
    
    try {
      const cleanResponse = response.replace(/```json\n?/g, '').replace(/```\n?/g, '').trim();
      return JSON.parse(cleanResponse);
    } catch (e) {
      const titleMatch = response.match(/"title":\s*"([^"]+)"/);
      const subtitleMatch = response.match(/"subtitle":\s*"([^"]+)"/);
      const contentMatch = response.match(/"content":\s*"([\s\S]+?)"\s*}/);
      return {
        title: titleMatch ? titleMatch[1] : 'Processo no STF',
        subtitle: subtitleMatch ? subtitleMatch[1] : '',
        content: contentMatch ? contentMatch[1].replace(/\\n/g, '\n').replace(/\\"/g, '"') : response
      };
    }
  }

  async getAISettings() {
    const settings = {};
    const keys = ['ai_enabled', 'ai_api_key', 'ai_api_url', 'ai_model'];
    for (const key of keys) {
      const setting = await Setting.findOne({ where: { key } });
      settings[key] = setting ? setting.value : null;
    }
    return settings;
  }
}

module.exports = new STFController();
