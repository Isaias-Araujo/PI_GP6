const mysql = require('mysql2/promise');
const bcrypt = require('bcrypt');

module.exports = async function () {

  const conn = await mysql.createConnection({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD
  });

  await conn.query(`CREATE DATABASE IF NOT EXISTS ${process.env.DB_NAME}`);

  await conn.query(`USE ${process.env.DB_NAME}`);

  await conn.query(`
    CREATE TABLE IF NOT EXISTS usuarios (
      id INT AUTO_INCREMENT PRIMARY KEY,
      nome VARCHAR(100),
      email VARCHAR(100) UNIQUE,
      senha_hash VARCHAR(255)
    )
  `);

  await conn.query(`
    CREATE TABLE IF NOT EXISTS tarefas (
      id INT AUTO_INCREMENT PRIMARY KEY,

      usuario_id INT,

      titulo VARCHAR(255),

      descricao TEXT,

      status VARCHAR(20) DEFAULT 'pending',

      concluido INT DEFAULT 0,

      tempo_gasto INT DEFAULT 0,

      data_inicio DATETIME NULL,

      data_fim DATETIME NULL,

      data_entrega DATETIME NULL
    )
  `);

  const [rows] = await conn.query('SELECT * FROM usuarios');

  if(rows.length === 0){

    const senha = await bcrypt.hash('senha123',10);

    await conn.query(
      'INSERT INTO usuarios(nome,email,senha_hash) VALUES (?,?,?)',
      ['Admin','admin@example.com',senha]
    );
  }

  console.log('Banco configurado automaticamente');
}