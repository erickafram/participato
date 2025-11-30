/**
 * Model Setting - Configurações gerais do site
 */
const { DataTypes } = require('sequelize');

module.exports = (sequelize) => {
  const Setting = sequelize.define('Setting', {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true
    },
    key: {
      type: DataTypes.STRING(100),
      allowNull: false,
      unique: true
    },
    value: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    type: {
      type: DataTypes.ENUM('text', 'textarea', 'number', 'boolean', 'json', 'image'),
      defaultValue: 'text'
    },
    group: {
      type: DataTypes.STRING(50),
      defaultValue: 'general'
    },
    label: {
      type: DataTypes.STRING(100),
      allowNull: true
    },
    description: {
      type: DataTypes.STRING(255),
      allowNull: true
    }
  }, {
    tableName: 'settings',
    timestamps: true,
    createdAt: 'created_at',
    updatedAt: 'updated_at'
  });

  // Método estático para obter configuração por chave
  Setting.get = async function(key, defaultValue = null) {
    const setting = await this.findOne({ where: { key } });
    if (!setting) return defaultValue;
    
    // Converter valor baseado no tipo
    switch (setting.type) {
      case 'boolean':
        return setting.value === 'true' || setting.value === '1';
      case 'number':
        return Number(setting.value);
      case 'json':
        try {
          return JSON.parse(setting.value);
        } catch {
          return defaultValue;
        }
      default:
        return setting.value;
    }
  };

  // Método estático para definir configuração
  Setting.set = async function(key, value, options = {}) {
    const [setting, created] = await this.findOrCreate({
      where: { key },
      defaults: {
        value: typeof value === 'object' ? JSON.stringify(value) : String(value),
        ...options
      }
    });
    
    if (!created) {
      setting.value = typeof value === 'object' ? JSON.stringify(value) : String(value);
      await setting.save();
    }
    
    return setting;
  };

  // Método estático para obter todas as configurações de um grupo
  Setting.getGroup = async function(group) {
    const settings = await this.findAll({ where: { group } });
    const result = {};
    settings.forEach(s => {
      result[s.key] = s.value;
    });
    return result;
  };

  return Setting;
};
