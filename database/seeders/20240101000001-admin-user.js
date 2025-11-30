'use strict';
const bcrypt = require('bcryptjs');

/** @type {import('sequelize-cli').Migration} */
module.exports = {
  async up(queryInterface, Sequelize) {
    const passwordHash = await bcrypt.hash('admin123', 10);
    
    await queryInterface.bulkInsert('users', [
      {
        name: 'Administrador',
        email: 'admin@portal.com',
        password_hash: passwordHash,
        role: 'admin',
        active: true,
        created_at: new Date(),
        updated_at: new Date()
      },
      {
        name: 'Editor',
        email: 'editor@portal.com',
        password_hash: passwordHash,
        role: 'editor',
        active: true,
        created_at: new Date(),
        updated_at: new Date()
      }
    ]);
  },

  async down(queryInterface, Sequelize) {
    await queryInterface.bulkDelete('users', null, {});
  }
};
