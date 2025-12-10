/**
 * Model PetitionSignature - Assinaturas de petições
 */
const { DataTypes } = require('sequelize');

module.exports = (sequelize) => {
  const PetitionSignature = sequelize.define('PetitionSignature', {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true
    },
    petition_id: {
      type: DataTypes.INTEGER,
      allowNull: false
    },
    user_id: {
      type: DataTypes.INTEGER,
      allowNull: true,
      comment: 'Null se assinatura anônima'
    },
    name: {
      type: DataTypes.STRING(100),
      allowNull: false
    },
    email: {
      type: DataTypes.STRING(255),
      allowNull: false
    },
    city: {
      type: DataTypes.STRING(100),
      allowNull: true
    },
    state: {
      type: DataTypes.STRING(50),
      allowNull: true
    },
    comment: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    is_public: {
      type: DataTypes.BOOLEAN,
      defaultValue: true,
      comment: 'Se o nome aparece publicamente'
    },
    verified: {
      type: DataTypes.BOOLEAN,
      defaultValue: false
    },
    verification_token: {
      type: DataTypes.STRING(100),
      allowNull: true
    },
    ip_address: {
      type: DataTypes.STRING(45),
      allowNull: true
    }
  }, {
    tableName: 'petition_signatures',
    timestamps: true,
    createdAt: 'created_at',
    updatedAt: 'updated_at',
    indexes: [
      {
        unique: true,
        fields: ['petition_id', 'email']
      }
    ]
  });

  return PetitionSignature;
};
