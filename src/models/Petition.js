/**
 * Model Petition - Petições públicas
 */
const { DataTypes } = require('sequelize');

module.exports = (sequelize) => {
  const Petition = sequelize.define('Petition', {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true
    },
    title: {
      type: DataTypes.STRING(255),
      allowNull: false
    },
    slug: {
      type: DataTypes.STRING(255),
      allowNull: false,
      unique: true
    },
    description: {
      type: DataTypes.TEXT,
      allowNull: false
    },
    content: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    image: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    goal: {
      type: DataTypes.INTEGER,
      allowNull: false,
      defaultValue: 1000,
      comment: 'Meta de assinaturas'
    },
    author_id: {
      type: DataTypes.INTEGER,
      allowNull: true,
      comment: 'Admin que criou (se criada pelo admin)'
    },
    petitioner_id: {
      type: DataTypes.INTEGER,
      allowNull: true,
      comment: 'Cidadão que criou a petição'
    },
    admin_notes: {
      type: DataTypes.TEXT,
      allowNull: true,
      comment: 'Notas do admin sobre aprovação/rejeição'
    },
    rejection_reason: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    approved_at: {
      type: DataTypes.DATE,
      allowNull: true
    },
    approved_by: {
      type: DataTypes.INTEGER,
      allowNull: true
    },
    category: {
      type: DataTypes.STRING(100),
      allowNull: true
    },
    status: {
      type: DataTypes.ENUM('pending', 'draft', 'active', 'closed', 'victory', 'rejected'),
      defaultValue: 'pending',
      comment: 'pending=aguardando aprovação, draft=rascunho admin, active=ativa, rejected=rejeitada'
    },
    featured: {
      type: DataTypes.BOOLEAN,
      defaultValue: false
    },
    allow_anonymous: {
      type: DataTypes.BOOLEAN,
      defaultValue: true,
      comment: 'Permite assinatura só com email'
    },
    show_signatures: {
      type: DataTypes.BOOLEAN,
      defaultValue: true,
      comment: 'Mostrar lista de assinaturas'
    },
    end_date: {
      type: DataTypes.DATE,
      allowNull: true
    },
    views: {
      type: DataTypes.INTEGER,
      defaultValue: 0
    }
  }, {
    tableName: 'petitions',
    timestamps: true,
    createdAt: 'created_at',
    updatedAt: 'updated_at'
  });

  return Petition;
};
