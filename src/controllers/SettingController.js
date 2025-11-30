/**
 * Controller de Configurações
 * Gerenciamento das configurações do site
 */
const { Setting } = require('../models');
const { clearSettingsCache } = require('../middlewares/settings');

class SettingController {
  // Exibir página de configurações
  async index(req, res) {
    try {
      const settings = await Setting.findAll({
        order: [['group', 'ASC'], ['key', 'ASC']]
      });

      // Agrupar configurações
      const groupedSettings = {};
      settings.forEach(setting => {
        if (!groupedSettings[setting.group]) {
          groupedSettings[setting.group] = [];
        }
        groupedSettings[setting.group].push(setting);
      });

      res.render('admin/settings/index', {
        title: 'Configurações',
        groupedSettings,
        groups: {
          general: 'Geral',
          contact: 'Contato',
          social: 'Redes Sociais',
          posts: 'Posts',
          footer: 'Rodapé'
        }
      });
    } catch (error) {
      console.error('Erro ao carregar configurações:', error);
      req.flash('error', 'Erro ao carregar configurações.');
      res.redirect('/admin');
    }
  }

  // Salvar configurações
  async update(req, res) {
    try {
      const settings = req.body;

      // Atualizar cada configuração
      for (const [key, value] of Object.entries(settings)) {
        if (key.startsWith('_')) continue; // Ignorar campos especiais

        await Setting.update(
          { value: value || '' },
          { where: { key } }
        );
      }

      // Limpar cache de configurações
      clearSettingsCache();

      req.flash('success', 'Configurações salvas com sucesso!');
      res.redirect('/admin/settings');
    } catch (error) {
      console.error('Erro ao salvar configurações:', error);
      req.flash('error', 'Erro ao salvar configurações: ' + error.message);
      res.redirect('/admin/settings');
    }
  }

  // Criar nova configuração (API)
  async create(req, res) {
    try {
      const { key, value, type, group, label, description } = req.body;

      // Verificar se já existe
      const existing = await Setting.findOne({ where: { key } });
      if (existing) {
        return res.status(400).json({
          success: false,
          message: 'Esta chave de configuração já existe.'
        });
      }

      const setting = await Setting.create({
        key,
        value,
        type: type || 'text',
        group: group || 'general',
        label,
        description
      });

      // Limpar cache
      clearSettingsCache();

      return res.json({
        success: true,
        message: 'Configuração criada com sucesso!',
        setting
      });
    } catch (error) {
      console.error('Erro ao criar configuração:', error);
      return res.status(500).json({
        success: false,
        message: 'Erro ao criar configuração.'
      });
    }
  }

  // Excluir configuração (API)
  async destroy(req, res) {
    try {
      const setting = await Setting.findByPk(req.params.id);

      if (!setting) {
        return res.status(404).json({
          success: false,
          message: 'Configuração não encontrada.'
        });
      }

      await setting.destroy();

      // Limpar cache
      clearSettingsCache();

      return res.json({
        success: true,
        message: 'Configuração excluída com sucesso!'
      });
    } catch (error) {
      console.error('Erro ao excluir configuração:', error);
      return res.status(500).json({
        success: false,
        message: 'Erro ao excluir configuração.'
      });
    }
  }
}

module.exports = new SettingController();
