-- update_schema.sql
-- Add columns required to track task status and accumulated time.
ALTER TABLE tarefas 
ADD COLUMN status ENUM('pending','running','paused','completed') DEFAULT 'pending',
ADD COLUMN tempo_gasto INT DEFAULT 0;
