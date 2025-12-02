/**
 * Controller de Mídias
 * Gerenciamento de uploads e biblioteca de mídia
 */
const { Media, User } = require('../models');
const { deleteImage } = require('../middlewares/upload');
const path = require('path');
const fs = require('fs');

class MediaController {
  // Listar todas as mídias (admin)
  async index(req, res) {
    try {
      const page = parseInt(req.query.page) || 1;
      const limit = 24;
      const offset = (page - 1) * limit;

      const { count, rows: medias } = await Media.findAndCountAll({
        include: [{ model: User, as: 'user', attributes: ['id', 'name'] }],
        order: [['created_at', 'DESC']],
        limit,
        offset
      });

      const totalPages = Math.ceil(count / limit);

      res.render('admin/media/index', {
        title: 'Biblioteca de Mídia',
        medias,
        pagination: {
          page,
          totalPages,
          total: count,
          hasNext: page < totalPages,
          hasPrev: page > 1
        }
      });
    } catch (error) {
      console.error('Erro ao listar mídias:', error);
      req.flash('error', 'Erro ao carregar biblioteca de mídia.');
      res.redirect('/admin');
    }
  }

  // Upload de mídia
  async upload(req, res) {
    try {
      if (!req.processedFile) {
        return res.status(400).json({ 
          success: false, 
          message: 'Nenhum arquivo enviado.' 
        });
      }

      const media = await Media.create({
        filename: req.processedFile.filename,
        original_name: req.processedFile.originalName,
        path: req.processedFile.path,
        url: req.processedFile.url,
        mimetype: req.processedFile.mimetype,
        size: req.processedFile.size,
        width: req.processedFile.width,
        height: req.processedFile.height,
        user_id: req.session.user.id
      });

      return res.json({
        success: true,
        message: 'Arquivo enviado com sucesso!',
        media: {
          id: media.id,
          url: media.url,
          thumbnailUrl: req.processedFile.thumbnailUrl,
          filename: media.filename,
          size: media.getFormattedSize()
        }
      });
    } catch (error) {
      console.error('Erro ao fazer upload:', error);
      return res.status(500).json({ 
        success: false, 
        message: 'Erro ao fazer upload: ' + error.message 
      });
    }
  }

  // Upload múltiplo de mídias
  async uploadMultiple(req, res) {
    try {
      if (!req.processedFiles || req.processedFiles.length === 0) {
        return res.status(400).json({ 
          success: false, 
          message: 'Nenhum arquivo enviado.' 
        });
      }

      const medias = await Promise.all(
        req.processedFiles.map(file => 
          Media.create({
            filename: file.filename,
            original_name: file.originalName,
            path: file.path,
            url: file.url,
            mimetype: file.mimetype,
            size: file.size,
            width: file.width,
            height: file.height,
            user_id: req.session.user.id
          })
        )
      );

      return res.json({
        success: true,
        message: `${medias.length} arquivo(s) enviado(s) com sucesso!`,
        medias: medias.map((media, index) => ({
          id: media.id,
          url: media.url,
          thumbnailUrl: req.processedFiles[index].thumbnailUrl,
          filename: media.filename
        }))
      });
    } catch (error) {
      console.error('Erro ao fazer upload:', error);
      return res.status(500).json({ 
        success: false, 
        message: 'Erro ao fazer upload: ' + error.message 
      });
    }
  }

  // Obter detalhes de uma mídia
  async show(req, res) {
    try {
      const media = await Media.findByPk(req.params.id, {
        include: [{ model: User, as: 'user', attributes: ['id', 'name'] }]
      });

      if (!media) {
        return res.status(404).json({ 
          success: false, 
          message: 'Mídia não encontrada.' 
        });
      }

      return res.json({
        success: true,
        media: {
          ...media.toJSON(),
          formattedSize: media.getFormattedSize()
        }
      });
    } catch (error) {
      console.error('Erro ao carregar mídia:', error);
      return res.status(500).json({ 
        success: false, 
        message: 'Erro ao carregar mídia.' 
      });
    }
  }

  // Atualizar informações da mídia
  async update(req, res) {
    try {
      const media = await Media.findByPk(req.params.id);

      if (!media) {
        return res.status(404).json({ 
          success: false, 
          message: 'Mídia não encontrada.' 
        });
      }

      const { alt_text, caption } = req.body;

      media.alt_text = alt_text;
      media.caption = caption;
      await media.save();

      return res.json({
        success: true,
        message: 'Mídia atualizada com sucesso!',
        media
      });
    } catch (error) {
      console.error('Erro ao atualizar mídia:', error);
      return res.status(500).json({ 
        success: false, 
        message: 'Erro ao atualizar mídia.' 
      });
    }
  }

  // Excluir mídia
  async destroy(req, res) {
    try {
      const media = await Media.findByPk(req.params.id);

      if (!media) {
        return res.status(404).json({ 
          success: false, 
          message: 'Mídia não encontrada.' 
        });
      }

      // Guardar filename antes de deletar
      const filename = media.filename;

      // Deletar registro do banco primeiro
      await media.destroy();

      // Depois deletar arquivos físicos (não crítico se falhar)
      try {
        deleteImage(filename);
      } catch (fileError) {
        console.error('Erro ao deletar arquivo físico:', fileError);
      }

      return res.json({
        success: true,
        message: 'Mídia excluída com sucesso!'
      });
    } catch (error) {
      console.error('Erro ao excluir mídia:', error);
      return res.status(500).json({ 
        success: false, 
        message: 'Erro ao excluir mídia: ' + error.message
      });
    }
  }

  // Listar mídias para seleção (modal)
  async browse(req, res) {
    try {
      const page = parseInt(req.query.page) || 1;
      const limit = 20;
      const offset = (page - 1) * limit;

      const { count, rows: medias } = await Media.findAndCountAll({
        order: [['created_at', 'DESC']],
        limit,
        offset
      });

      const totalPages = Math.ceil(count / limit);

      return res.json({
        success: true,
        medias: medias.map(m => ({
          id: m.id,
          url: m.url,
          filename: m.filename,
          alt_text: m.alt_text
        })),
        pagination: {
          page,
          totalPages,
          hasNext: page < totalPages,
          hasPrev: page > 1
        }
      });
    } catch (error) {
      console.error('Erro ao listar mídias:', error);
      return res.status(500).json({ 
        success: false, 
        message: 'Erro ao carregar mídias.' 
      });
    }
  }
}

module.exports = new MediaController();
