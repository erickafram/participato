/**
 * Controller de Assistente de IA
 * Integração com Together AI para criação de conteúdo
 */
const { Setting } = require('../models');
const https = require('https');
const http = require('http');

class AIController {
  // Buscar notícias reais via Google News RSS
  async searchWeb(query) {
    return new Promise((resolve, reject) => {
      const googleNewsUrl = `https://news.google.com/rss/search?q=${encodeURIComponent(query)}&hl=pt-BR&gl=BR&ceid=BR:pt-419`;
      
      https.get(googleNewsUrl, {
        headers: {
          'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
      }, (res) => {
        let data = '';
        res.on('data', chunk => data += chunk);
        res.on('end', () => {
          try {
            const results = [];
            const itemRegex = /<item>([\s\S]*?)<\/item>/gi;
            let match;
            
            while ((match = itemRegex.exec(data)) !== null && results.length < 6) {
              const item = match[1];
              
              const titleMatch = item.match(/<title>([^<]+)/i);
              const pubDateMatch = item.match(/<pubDate>([^<]+)/i);
              const sourceMatch = item.match(/<source[^>]*>([^<]+)/i);
              const descMatch = item.match(/<description>([^<]+)/i);
              
              if (titleMatch) {
                const title = titleMatch[1].replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&#39;/g, "'");
                const source = sourceMatch ? sourceMatch[1] : 'Fonte desconhecida';
                const date = pubDateMatch ? this.formatDate(pubDateMatch[1]) : '';
                const desc = descMatch ? descMatch[1].replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>') : '';
                
                results.push(`FONTE: ${source}\nTÍTULO: ${title}\nDATA: ${date}${desc ? '\nRESUMO: ' + desc : ''}`);
              }
            }
            
            if (results.length === 0) {
              console.log('Nenhuma notícia encontrada no Google News');
              resolve('');
            } else {
              const hoje = new Date().toLocaleDateString('pt-BR');
              resolve(`NOTÍCIAS REAIS ENCONTRADAS (pesquisa em ${hoje}):\n\n${results.join('\n\n---\n\n')}\n\n⚠️ IMPORTANTE: Baseie a matéria APENAS nestas notícias reais. NÃO invente informações adicionais.`);
            }
          } catch (e) {
            console.error('Erro ao processar Google News:', e);
            resolve('');
          }
        });
      }).on('error', (e) => {
        console.error('Erro Google News:', e);
        resolve('');
      });
    });
  }

  // Formatar data do RSS para português
  formatDate(dateStr) {
    try {
      const date = new Date(dateStr);
      return date.toLocaleDateString('pt-BR', { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    } catch (e) {
      return dateStr;
    }
  }

  // Chamar API Together AI
  async callTogetherAI(prompt, settings) {
    return new Promise((resolve, reject) => {
      const apiKey = settings.ai_api_key;
      const apiUrl = settings.ai_api_url || 'https://api.together.xyz/v1/chat/completions';
      const model = settings.ai_model || 'meta-llama/Llama-3-70b-chat-hf';

      const urlParts = new URL(apiUrl);
      const isHttps = urlParts.protocol === 'https:';
      const httpModule = isHttps ? https : http;

      const systemPrompt = `Você é um jornalista investigativo do portal Metrópoles, um dos maiores veículos de notícias do Brasil.

REGRA MAIS IMPORTANTE - NUNCA QUEBRE:
⚠️ JAMAIS INVENTE informações, nomes, datas, números ou fatos
⚠️ Use APENAS dados fornecidos na pesquisa ou informados pelo usuário
⚠️ Se não tiver dados suficientes, use marcadores [PREENCHER] ou informe a limitação
⚠️ Datas devem ser coerentes com a data atual informada

ESTILO METRÓPOLES:
- Linguagem direta, objetiva e factual
- Primeiro parágrafo (lide) resume o fato principal
- Parágrafos curtos (3-4 linhas máximo)
- Citações entre aspas quando houver falas
- Tom investigativo, nunca opinativo
- Estrutura: Lide > Desenvolvimento > Contexto
- Português brasileiro formal

PROIBIDO:
- Inventar nomes de pessoas, empresas ou órgãos
- Criar datas ou números fictícios
- Fabricar citações ou declarações
- Usar linguagem sensacionalista`;

      const requestData = JSON.stringify({
        model: model,
        messages: [
          { role: 'system', content: systemPrompt },
          { role: 'user', content: prompt }
        ],
        max_tokens: 4000,
        temperature: 0.7
      });

      const options = {
        hostname: urlParts.hostname,
        port: urlParts.port || (isHttps ? 443 : 80),
        path: urlParts.pathname,
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${apiKey}`,
          'Content-Length': Buffer.byteLength(requestData)
        }
      };

      const req = httpModule.request(options, (res) => {
        let data = '';
        res.on('data', chunk => data += chunk);
        res.on('end', () => {
          try {
            // Verificar se a resposta é HTML (erro)
            if (data.trim().startsWith('<!DOCTYPE') || data.trim().startsWith('<html')) {
              console.error('API retornou HTML em vez de JSON. Status:', res.statusCode);
              console.error('URL:', apiUrl);
              reject(new Error(`Erro na API (Status ${res.statusCode}). Verifique a URL e a chave da API nas configurações.`));
              return;
            }
            
            // Verificar status HTTP
            if (res.statusCode !== 200) {
              console.error('API retornou status:', res.statusCode);
              console.error('Resposta:', data.substring(0, 500));
              reject(new Error(`Erro na API: Status ${res.statusCode}`));
              return;
            }
            
            const json = JSON.parse(data);
            if (json.choices && json.choices[0]) {
              resolve(json.choices[0].message.content);
            } else if (json.error) {
              reject(new Error(json.error.message || 'Erro na API'));
            } else {
              console.error('Resposta inesperada:', JSON.stringify(json).substring(0, 500));
              reject(new Error('Resposta inválida da API'));
            }
          } catch (e) {
            console.error('Erro ao processar resposta:', e.message);
            console.error('Dados recebidos:', data.substring(0, 300));
            reject(new Error('Erro ao processar resposta da API. Verifique as configurações.'));
          }
        });
      });

      req.on('error', (e) => {
        console.error('Erro de conexão:', e.message);
        reject(new Error('Erro de conexão com a API: ' + e.message));
      });
      
      req.setTimeout(60000, () => {
        req.destroy();
        reject(new Error('Timeout: A API demorou muito para responder'));
      });
      
      req.write(requestData);
      req.end();
    });
  }

  // Obter configurações de IA
  async getAISettings() {
    const settings = {};
    const keys = ['ai_enabled', 'ai_api_key', 'ai_api_url', 'ai_model'];
    
    for (const key of keys) {
      const setting = await Setting.findOne({ where: { key } });
      settings[key] = setting ? setting.value : null;
    }
    
    return settings;
  }

  // Gerar matéria completa
  async generateArticle(req, res) {
    try {
      const settings = await this.getAISettings();
      
      if (settings.ai_enabled !== 'true' && settings.ai_enabled !== '1') {
        return res.status(400).json({ success: false, message: 'Assistente de IA está desativado.' });
      }
      
      if (!settings.ai_api_key) {
        return res.status(400).json({ success: false, message: 'Chave da API não configurada.' });
      }

      const { keyword, searchWeb } = req.body;
      
      if (!keyword) {
        return res.status(400).json({ success: false, message: 'Palavra-chave é obrigatória.' });
      }

      let context = '';
      let hasRealData = false;
      
      if (searchWeb) {
        context = await this.searchWeb(keyword);
        hasRealData = context && context.length > 100;
      }

      // Data atual para referência
      const hoje = new Date();
      const dataAtual = hoje.toLocaleDateString('pt-BR', { day: 'numeric', month: 'long', year: 'numeric' });

      let prompt;
      
      if (hasRealData) {
        prompt = `DATA DE HOJE: ${dataAtual}

NOTÍCIAS REAIS ENCONTRADAS NA PESQUISA:
${context}

TAREFA: Crie uma matéria jornalística COMPLETA e DETALHADA no estilo Metrópoles sobre: "${keyword}"

INSTRUÇÕES PARA CONTEÚDO EXTENSO:
- Desenvolva uma matéria com NO MÍNIMO 8-10 parágrafos
- Cada parágrafo deve ter 3-4 linhas
- Use TODAS as informações disponíveis nas notícias acima
- Combine informações de diferentes fontes quando possível
- Adicione contexto explicativo para o leitor entender o assunto
- Inclua possíveis desdobramentos ou consequências do fato
- Se houver múltiplas notícias, faça uma matéria abrangente

ESTRUTURA OBRIGATÓRIA:
1. LIDE (1º parágrafo): Resuma o fato principal de forma impactante
2. DETALHAMENTO (2-3 parágrafos): Desenvolva os detalhes do acontecimento
3. CONTEXTO (2-3 parágrafos): Explique o contexto, histórico ou background
4. REPERCUSSÃO (1-2 parágrafos): Reações, declarações ou posicionamentos
5. DESDOBRAMENTOS (1-2 parágrafos): O que pode acontecer, investigações, etc.

REGRAS:
- Use APENAS fatos das notícias encontradas
- NÃO invente nomes, datas ou números
- Reescreva com suas palavras (não copie)
- Use citações entre aspas quando apropriado
- Hoje é ${dataAtual}

Retorne EXATAMENTE neste formato JSON:
{
  "title": "título impactante baseado nos fatos",
  "subtitle": "subtítulo explicativo com detalhes importantes",
  "content": "conteúdo EXTENSO em HTML com múltiplos <p>, mínimo 8 parágrafos bem desenvolvidos"
}`;
      } else {
        prompt = `DATA DE HOJE: ${dataAtual}

TAREFA: Crie uma matéria jornalística COMPLETA sobre "${keyword}" no estilo Metrópoles.

Como não há dados específicos da pesquisa, crie uma matéria informativa e educativa sobre o tema, usando conhecimento geral. Seja abrangente e detalhado.

INSTRUÇÕES:
- Escreva NO MÍNIMO 8-10 parágrafos completos
- Aborde o tema de forma ampla e informativa
- Inclua contexto, explicações e informações relevantes
- Use linguagem jornalística profissional
- Se for um tema de notícia, use estrutura de matéria factual
- Se for um tema geral, faça uma matéria explicativa/informativa

ESTRUTURA:
1. LIDE: Introdução impactante ao tema
2. DESENVOLVIMENTO: Explicação detalhada (3-4 parágrafos)
3. CONTEXTO: Background e informações complementares (2-3 parágrafos)
4. ANÁLISE: Importância, impactos ou relevância do tema (2 parágrafos)
5. CONCLUSÃO: Fechamento ou perspectivas

IMPORTANTE:
- NÃO invente nomes de pessoas específicas, datas exatas ou números fictícios
- Use termos genéricos quando necessário: "autoridades", "especialistas afirmam", "segundo fontes"
- Seja informativo e útil ao leitor

Retorne EXATAMENTE neste formato JSON:
{
  "title": "título informativo e chamativo sobre ${keyword}",
  "subtitle": "subtítulo que complementa e explica o tema",
  "content": "conteúdo EXTENSO em HTML com múltiplos <p>, mínimo 8 parágrafos bem desenvolvidos"
}`; 
      }


      const response = await this.callTogetherAI(prompt, settings);
      
      // Tentar extrair JSON da resposta
      let result;
      try {
        // Remover possíveis marcadores de código
        const cleanResponse = response.replace(/```json\n?/g, '').replace(/```\n?/g, '').trim();
        result = JSON.parse(cleanResponse);
      } catch (e) {
        // Se não for JSON válido, tentar extrair manualmente
        const titleMatch = response.match(/"title":\s*"([^"]+)"/);
        const subtitleMatch = response.match(/"subtitle":\s*"([^"]+)"/);
        const contentMatch = response.match(/"content":\s*"([\s\S]+?)"\s*}/);
        
        result = {
          title: titleMatch ? titleMatch[1] : keyword,
          subtitle: subtitleMatch ? subtitleMatch[1] : '',
          content: contentMatch ? contentMatch[1].replace(/\\n/g, '\n').replace(/\\"/g, '"') : response
        };
      }

      return res.json({ success: true, data: result });
    } catch (error) {
      console.error('Erro ao gerar artigo:', error);
      return res.status(500).json({ success: false, message: error.message });
    }
  }

  // Melhorar/corrigir título
  async improveTitle(req, res) {
    try {
      const settings = await this.getAISettings();
      
      if (settings.ai_enabled !== 'true' && settings.ai_enabled !== '1') {
        return res.status(400).json({ success: false, message: 'Assistente de IA está desativado.' });
      }

      const { title } = req.body;
      if (!title) {
        return res.status(400).json({ success: false, message: 'Título é obrigatório.' });
      }

      const prompt = `Reescreva este título no estilo do portal Metrópoles.

CARACTERÍSTICAS DO TÍTULO METRÓPOLES:
- Direto e informativo, vai direto ao ponto
- Usa verbos de ação no presente ou passado
- Pode usar vírgula para adicionar contexto (ex: "Ministro nega acusações, mas provas contradizem versão")
- Evita clickbait exagerado, mas é chamativo
- Menciona os envolvidos principais
- Máximo 15 palavras

Título original: "${title}"

Responda APENAS com o novo título, sem aspas, sem explicações.`;

      const response = await this.callTogetherAI(prompt, settings);
      return res.json({ success: true, data: response.trim().replace(/^["']|["']$/g, '') });
    } catch (error) {
      console.error('Erro ao melhorar título:', error);
      return res.status(500).json({ success: false, message: error.message });
    }
  }

  // Melhorar/corrigir subtítulo
  async improveSubtitle(req, res) {
    try {
      const settings = await this.getAISettings();
      
      if (settings.ai_enabled !== 'true' && settings.ai_enabled !== '1') {
        return res.status(400).json({ success: false, message: 'Assistente de IA está desativado.' });
      }

      const { subtitle, title } = req.body;
      if (!subtitle) {
        return res.status(400).json({ success: false, message: 'Subtítulo é obrigatório.' });
      }

      const prompt = `Reescreva este subtítulo no estilo do portal Metrópoles.

CARACTERÍSTICAS DO SUBTÍTULO METRÓPOLES:
- Complementa o título com informações adicionais
- Explica o "como" ou "por quê" da notícia
- Uma ou duas frases curtas e diretas
- Pode mencionar fontes ou órgãos envolvidos
- Adiciona contexto que não coube no título

${title ? `Título da matéria: "${title}"` : ''}
Subtítulo original: "${subtitle}"

Responda APENAS com o novo subtítulo, sem aspas, sem explicações.`;

      const response = await this.callTogetherAI(prompt, settings);
      return res.json({ success: true, data: response.trim().replace(/^["']|["']$/g, '') });
    } catch (error) {
      console.error('Erro ao melhorar subtítulo:', error);
      return res.status(500).json({ success: false, message: error.message });
    }
  }

  // Melhorar/corrigir conteúdo
  async improveContent(req, res) {
    try {
      const settings = await this.getAISettings();
      
      if (settings.ai_enabled !== 'true' && settings.ai_enabled !== '1') {
        return res.status(400).json({ success: false, message: 'Assistente de IA está desativado.' });
      }

      const { content, title } = req.body;
      if (!content) {
        return res.status(400).json({ success: false, message: 'Conteúdo é obrigatório.' });
      }

      const prompt = `Reescreva e EXPANDA este conteúdo no estilo jornalístico do portal Metrópoles.

INSTRUÇÕES IMPORTANTES:
1. MANTENHA todas as informações originais
2. EXPANDA o conteúdo adicionando:
   - Mais contexto e explicações
   - Transições entre parágrafos
   - Detalhes que enriqueçam a narrativa
3. O resultado deve ter NO MÍNIMO o dobro de parágrafos do original
4. Cada parágrafo deve ter 3-4 linhas completas

ESTILO METRÓPOLES:
- Lide forte no primeiro parágrafo
- Parágrafos bem desenvolvidos
- Linguagem direta e factual
- Citações entre aspas
- Tom investigativo
- Corrija erros gramaticais
- Estrutura: Lide > Desenvolvimento > Contexto > Desdobramentos

${title ? `Título da matéria: "${title}"` : ''}

Conteúdo original para expandir:
${content}

Responda APENAS com o conteúdo reescrito e EXPANDIDO em HTML (usando <p> para parágrafos, <strong> para destaques), sem explicações. O conteúdo deve ser significativamente maior que o original.`;

      const response = await this.callTogetherAI(prompt, settings);
      return res.json({ success: true, data: response.trim() });
    } catch (error) {
      console.error('Erro ao melhorar conteúdo:', error);
      return res.status(500).json({ success: false, message: error.message });
    }
  }

  // Verificar status da IA
  async status(req, res) {
    try {
      const settings = await this.getAISettings();
      return res.json({
        success: true,
        enabled: settings.ai_enabled === 'true' || settings.ai_enabled === '1',
        configured: !!settings.ai_api_key
      });
    } catch (error) {
      return res.json({ success: false, enabled: false, configured: false });
    }
  }
}

module.exports = new AIController();
