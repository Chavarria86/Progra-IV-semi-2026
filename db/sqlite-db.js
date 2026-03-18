/**
 * sqlite-db.js
 * Inicialización de SQLite WASM usando sqlite3-worker1-promiser
 * Persistencia mediante OPFS (Origin Private File System)
 * Sin uso de IndexedDB ni localStorage
 */

'use strict';

globalThis.dbReady = (async () => {
    const promiser = await new Promise((resolve) => {
        const _promiser = sqlite3Worker1Promiser({
            onready: () => resolve(_promiser),
        });
    });

    // Verificar si OPFS está disponible
    const configResponse = await promiser('config-get', {});
    const opfsAvailable = configResponse.result.bigIntEnabled !== undefined &&
        typeof globalThis.FileSystemHandle !== 'undefined';

    let dbId;

    try {
        if (opfsAvailable) {
            // Intentar abrir con OPFS para persistencia real sin IndexedDB
            const openResponse = await promiser('open', {
                filename: 'file:db_academica.sqlite3?vfs=opfs',
            });
            dbId = openResponse.result.dbId;
            console.log('[SQLite] Base de datos abierta con OPFS:', openResponse.result.filename);
        } else {
            throw new Error('OPFS no disponible, usando memoria');
        }
    } catch (e) {
        console.warn('[SQLite] OPFS no disponible, usando base de datos en memoria:', e.message);
        const openResponse = await promiser('open', { filename: ':memory:' });
        dbId = openResponse.result.dbId;
        console.log('[SQLite] Base de datos en memoria inicializada.');
    }

    // Exponer el promiser y dbId globalmente
    globalThis.sqlitePromiser = promiser;
    globalThis.sqliteDbId = dbId;

    // Habilitar claves foráneas
    await promiser('exec', { dbId, sql: 'PRAGMA foreign_keys = ON;' });

    // Crear tablas del sistema académico
    await promiser('exec', {
        dbId,
        sql: `
            CREATE TABLE IF NOT EXISTS alumnos (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                id_Alumno   TEXT NOT NULL UNIQUE,
                codigo      TEXT NOT NULL,
                nombre      TEXT NOT NULL,
                direccion   TEXT NOT NULL,
                telefono    TEXT NOT NULL,
                email       TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS materias (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                id_Materia  TEXT NOT NULL UNIQUE,
                codigo      TEXT NOT NULL,
                nombre      TEXT NOT NULL,
                creditos    INTEGER NOT NULL DEFAULT 0,
                descripcion TEXT NOT NULL DEFAULT ''
            );

            CREATE TABLE IF NOT EXISTS docentes (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                id_Docente  TEXT NOT NULL UNIQUE,
                codigo      TEXT NOT NULL,
                nombre      TEXT NOT NULL,
                especialidad TEXT NOT NULL DEFAULT '',
                telefono    TEXT NOT NULL,
                email       TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS matriculas (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                id_Matricula TEXT NOT NULL UNIQUE,
                id_Alumno    TEXT NOT NULL,
                ciclo        TEXT NOT NULL,
                fecha        TEXT NOT NULL,
                FOREIGN KEY (id_Alumno) REFERENCES alumnos(id_Alumno)
            );

            CREATE TABLE IF NOT EXISTS inscripciones (
                id              INTEGER PRIMARY KEY AUTOINCREMENT,
                id_Inscripcion  TEXT NOT NULL UNIQUE,
                id_Matricula    TEXT NOT NULL,
                id_Materia      TEXT NOT NULL,
                id_Docente      TEXT NOT NULL,
                FOREIGN KEY (id_Matricula) REFERENCES matriculas(id_Matricula),
                FOREIGN KEY (id_Materia)   REFERENCES materias(id_Materia),
                FOREIGN KEY (id_Docente)   REFERENCES docentes(id_Docente)
            );
        `
    });

    console.log('[SQLite] Tablas creadas/verificadas correctamente.');

    /**
     * Ejecuta una consulta SQL y devuelve un array de objetos.
     * @param {string} sql
     * @param {Array}  bind  - parámetros posicionales
     * @returns {Promise<Array>}
     */
    globalThis.dbQuery = async (sql, bind = []) => {
        const rows = [];
        await promiser('exec', {
            dbId,
            sql,
            bind,
            callback: (result) => {
                if (result.row) {
                    const obj = {};
                    result.columnNames.forEach((col, i) => { obj[col] = result.row[i]; });
                    rows.push(obj);
                }
            }
        });
        return rows;
    };

    /**
     * Ejecuta una sentencia DML (INSERT, UPDATE, DELETE).
     * @param {string} sql
     * @param {Array}  bind
     * @returns {Promise<void>}
     */
    globalThis.dbExec = async (sql, bind = []) => {
        await promiser('exec', { dbId, sql, bind });
    };

    return { promiser, dbId };
})();
