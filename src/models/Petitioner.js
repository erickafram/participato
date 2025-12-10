/**
 * Model Petitioner - Cidadãos que criam petições
 */
const { DataTypes } = require('sequelize');
const bcrypt = require('bcryptjs');

module.exports = (sequelize) => {
  const Petitioner = sequelize.define('Petitioner', {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true
    },
    name: {
      type: DataTypes.STRING(100),
      allowNull: false
    },
    email: {
      type: DataTypes.STRING(150),
      allowNull: false,
      unique: true
    },
    phone: {
      type: DataTypes.STRING(20),
      allowNull: false
    },
    password_hash: {
      type: DataTypes.STRING(255),
      allowNull: false
    },
    active: {
      type: DataTypes.BOOLEAN,
      defaultValue: true
    },
    email_verified: {
      type: DataTypes.BOOLEAN,
      defaultValue: false
    },
    verification_token: {
      type: DataTypes.STRING(100),
      allowNull: true
    },
    last_login: {
      type: DataTypes.DATE,
      allowNull: true
    }
  }, {
    tableName: 'petitioners',
    timestamps: true,
    createdAt: 'created_at',
    updatedAt: 'updated_at',
    hooks: {
      beforeCreate: async (petitioner) => {
        if (petitioner.password_hash) {
          petitioner.password_hash = await bcrypt.hash(petitioner.password_hash, 10);
        }
      },
      beforeUpdate: async (petitioner) => {
        if (petitioner.changed('password_hash')) {
          petitioner.password_hash = await bcrypt.hash(petitioner.password_hash, 10);
        }
      }
    }
  });

  // Verificar senha
  Petitioner.prototype.checkPassword = async function(password) {
    return bcrypt.compare(password, this.password_hash);
  };

  return Petitioner;
};
