/**
 * Controller de Subcategorias
 * CRUD completo de subcategorias
 */
const { Subcategory, Category } = require('../models');
const { Op } = require('sequelize');
const slugify = require('slugify');

class SubcategoryController {
    // Listar todas as subcategorias (admin)
    async index(req, res) {
        try {
            const subcategories = await Subcategory.findAll({
                order: [['category_id', 'ASC'], ['order', 'ASC'], ['name', 'ASC']],
                include: [{
                    model: Category,
                    as: 'category',
                    attributes: ['id', 'name', 'color']
                }]
            });

            const categories = await Category.findAll({
                where: { active: true },
                order: [['order', 'ASC'], ['name', 'ASC']]
            });

            res.render('admin/subcategories/index', {
                title: 'Subcategorias',
                subcategories,
                categories
            });
        } catch (error) {
            console.error('Erro ao listar subcategorias:', error);
            req.flash('error', 'Erro ao carregar subcategorias.');
            res.redirect('/admin');
        }
    }

    // Exibir formulário de criação
    async create(req, res) {
        try {
            const categories = await Category.findAll({
                where: { active: true },
                order: [['order', 'ASC'], ['name', 'ASC']]
            });

            res.render('admin/subcategories/form', {
                title: 'Nova Subcategoria',
                subcategory: null,
                categories,
                isEdit: false
            });
        } catch (error) {
            console.error('Erro ao carregar formulário:', error);
            req.flash('error', 'Erro ao carregar formulário.');
            res.redirect('/admin/subcategories');
        }
    }

    // Salvar nova subcategoria
    async store(req, res) {
        try {
            const { category_id, name, slug, description, color, icon, order, active } = req.body;

            // Verificar se categoria existe
            const category = await Category.findByPk(category_id);
            if (!category) {
                req.flash('error', 'Categoria não encontrada.');
                return res.redirect('/admin/subcategories/create');
            }

            // Gerar slug se não fornecido
            let subcategorySlug = slug || slugify(name, { lower: true, strict: true });

            // Verificar se slug já existe
            const existingSubcategory = await Subcategory.findOne({ where: { slug: subcategorySlug } });
            if (existingSubcategory) {
                req.flash('error', 'Este slug já está em uso.');
                return res.redirect('/admin/subcategories/create');
            }

            await Subcategory.create({
                category_id,
                name,
                slug: subcategorySlug,
                description,
                color: color || '#3ba4ff',
                icon,
                order: order || 0,
                active: active === 'on' || active === true
            });

            req.flash('success', 'Subcategoria criada com sucesso!');
            res.redirect('/admin/subcategories');
        } catch (error) {
            console.error('Erro ao criar subcategoria:', error);
            req.flash('error', 'Erro ao criar subcategoria: ' + error.message);
            res.redirect('/admin/subcategories/create');
        }
    }

    // Exibir formulário de edição
    async edit(req, res) {
        try {
            const subcategory = await Subcategory.findByPk(req.params.id, {
                include: [{
                    model: Category,
                    as: 'category'
                }]
            });

            if (!subcategory) {
                req.flash('error', 'Subcategoria não encontrada.');
                return res.redirect('/admin/subcategories');
            }

            const categories = await Category.findAll({
                where: { active: true },
                order: [['order', 'ASC'], ['name', 'ASC']]
            });

            res.render('admin/subcategories/form', {
                title: 'Editar Subcategoria',
                subcategory,
                categories,
                isEdit: true
            });
        } catch (error) {
            console.error('Erro ao carregar subcategoria:', error);
            req.flash('error', 'Erro ao carregar subcategoria.');
            res.redirect('/admin/subcategories');
        }
    }

    // Atualizar subcategoria
    async update(req, res) {
        try {
            const subcategory = await Subcategory.findByPk(req.params.id);

            if (!subcategory) {
                req.flash('error', 'Subcategoria não encontrada.');
                return res.redirect('/admin/subcategories');
            }

            const { category_id, name, slug, description, color, icon, order, active } = req.body;

            // Verificar se categoria existe
            const category = await Category.findByPk(category_id);
            if (!category) {
                req.flash('error', 'Categoria não encontrada.');
                return res.redirect(`/admin/subcategories/${subcategory.id}/edit`);
            }

            // Verificar slug único (se alterado)
            if (slug && slug !== subcategory.slug) {
                const existingSubcategory = await Subcategory.findOne({
                    where: { slug, id: { [Op.ne]: subcategory.id } }
                });
                if (existingSubcategory) {
                    req.flash('error', 'Este slug já está em uso.');
                    return res.redirect(`/admin/subcategories/${subcategory.id}/edit`);
                }
            }

            // Atualizar campos
            subcategory.category_id = category_id;
            subcategory.name = name;
            subcategory.slug = slug || subcategory.slug;
            subcategory.description = description;
            subcategory.color = color || '#3ba4ff';
            subcategory.icon = icon;
            subcategory.order = order || 0;
            subcategory.active = active === 'on' || active === true;

            await subcategory.save();

            req.flash('success', 'Subcategoria atualizada com sucesso!');
            res.redirect('/admin/subcategories');
        } catch (error) {
            console.error('Erro ao atualizar subcategoria:', error);
            req.flash('error', 'Erro ao atualizar subcategoria: ' + error.message);
            res.redirect(`/admin/subcategories/${req.params.id}/edit`);
        }
    }

    // Excluir subcategoria
    async destroy(req, res) {
        try {
            const subcategory = await Subcategory.findByPk(req.params.id);

            if (!subcategory) {
                req.flash('error', 'Subcategoria não encontrada.');
                return res.redirect('/admin/subcategories');
            }

            await subcategory.destroy();

            req.flash('success', 'Subcategoria excluída com sucesso!');
            res.redirect('/admin/subcategories');
        } catch (error) {
            console.error('Erro ao excluir subcategoria:', error);
            req.flash('error', 'Erro ao excluir subcategoria.');
            res.redirect('/admin/subcategories');
        }
    }

    // Alternar status ativo
    async toggleActive(req, res) {
        try {
            const subcategory = await Subcategory.findByPk(req.params.id);

            if (!subcategory) {
                return res.json({ success: false, message: 'Subcategoria não encontrada.' });
            }

            subcategory.active = !subcategory.active;
            await subcategory.save();

            return res.json({
                success: true,
                active: subcategory.active,
                message: subcategory.active ? 'Subcategoria ativada!' : 'Subcategoria desativada!'
            });
        } catch (error) {
            console.error('Erro ao alternar status:', error);
            return res.json({ success: false, message: 'Erro ao alternar status.' });
        }
    }
}

module.exports = new SubcategoryController();
