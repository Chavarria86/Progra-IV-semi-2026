// db/worker.js
importScripts('sqlite3.js');

let db;

// 1. Inicialización y creación de TODAS las tablas
const inicializarEstructura = async () => {
    const sqlite3 = await sqlite3InitModule();
    try {
        if ('opfs' in sqlite3) {
            db = new sqlite3.oo1.OpfsDb('/sistema_academico.db');
            console.log('✅ SQLite listo en OPFS:', db.filename);
        } else {
            db = new sqlite3.oo1.DB('/sistema_academico.db', 'ct');
            console.warn('⚠️ OPFS no disponible, usando memoria temporal.');
        }

        // --- EL CAMBIO ESTÁ AQUÍ: Añadimos la tabla 'docentes' ---
        db.exec(`
            CREATE TABLE IF NOT EXISTS alumnos (
                idAlumno INTEGER PRIMARY KEY,
                codigo TEXT UNIQUE NOT NULL,
                nombre TEXT NOT NULL,
                direccion TEXT,
                municipio TEXT,
                departamento TEXT,
                email TEXT,
                telefono TEXT,
                fechaNac TEXT,
                sexo TEXT
            );

            CREATE TABLE IF NOT EXISTS materias (
                idMateria INTEGER PRIMARY KEY,
                codigo TEXT UNIQUE NOT NULL,
                nombre TEXT NOT NULL,
                uv INTEGER NOT NULL
            );

            CREATE TABLE IF NOT EXISTS docentes (
                idDocente INTEGER PRIMARY KEY,
                codigo TEXT UNIQUE NOT NULL,
                nombre TEXT NOT NULL,
                direccion TEXT,
                email TEXT,
                telefono TEXT,
                escalafon TEXT
            );

            CREATE TABLE IF NOT EXISTS matriculas (
                idMatricula INTEGER PRIMARY KEY,
                idAlumno INTEGER NOT NULL,
                fecha TEXT NOT NULL,
                ciclo TEXT NOT NULL,
                FOREIGN KEY (idAlumno) REFERENCES alumnos(idAlumno) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS inscripciones (
                idInscripcion INTEGER PRIMARY KEY,
                idAlumno INTEGER NOT NULL,
                idMateria INTEGER NOT NULL,
                fecha TEXT NOT NULL,
                FOREIGN KEY (idAlumno) REFERENCES alumnos(idAlumno) ON DELETE CASCADE,
                FOREIGN KEY (idMateria) REFERENCES materias(idMateria) ON DELETE CASCADE
            );
        `);
        return true; 
    } catch (err) {
        console.error('❌ Error crítico en DB:', err.message);
        throw err;
    }
};

const dbLista = inicializarEstructura();

self.onmessage = async (e) => {
    await dbLista; 
    const { type, data } = e.data;

    try {
        // ==========================================
        // MÓDULO ALUMNOS
        // ==========================================
        if (type === 'GUARDAR_ALUMNO') {
            db.exec({
                sql: `INSERT OR REPLACE INTO alumnos VALUES (?,?,?,?,?,?,?,?,?,?)`,
                bind: [data.idAlumno, data.codigo, data.nombre, data.direccion, data.municipio, 
                       data.departamento, data.email, data.telefono, data.fechaNac, data.sexo]
            });
            self.postMessage({ type: 'SUCCESS_ALUMNO' });
        }
        
        if (type === 'BUSCAR_ALUMNOS') {
            const rows = [];
            let sql = "SELECT * FROM alumnos";
            let bind = [];
            if (data && data.termino) {
                sql += " WHERE codigo LIKE ? OR nombre LIKE ?";
                const likeTerm = `%${data.termino}%`;
                bind = [likeTerm, likeTerm];
            }
            db.exec({
                sql: sql, bind: bind,
                callback: (row) => {
                    rows.push({
                        idAlumno: row[0], codigo: row[1], nombre: row[2], direccion: row[3],
                        municipio: row[4], departamento: row[5], email: row[6],
                        telefono: row[7], fechaNac: row[8], sexo: row[9]
                    });
                }
            });
            self.postMessage({ type: 'RESULTADO_BUSQUEDA', data: rows });
        }

        if (type === 'ELIMINAR_ALUMNO') {
            db.exec({ sql: "DELETE FROM alumnos WHERE idAlumno = ?", bind: [data.idAlumno] });
            self.postMessage({ type: 'SUCCESS_ELIMINAR' });
        }

        // ==========================================
        // MÓDULO MATERIAS
        // ==========================================
        if (type === 'GUARDAR_MATERIA') {
            db.exec({
                sql: `INSERT OR REPLACE INTO materias VALUES (?,?,?,?)`,
                bind: [data.idMateria, data.codigo, data.nombre, data.uv]
            });
            self.postMessage({ type: 'SUCCESS_MATERIA' });
        }

        if (type === 'BUSCAR_MATERIAS') {
            const rows = [];
            let sql = "SELECT * FROM materias";
            let bind = [];
            if (data && data.termino) {
                sql += " WHERE codigo LIKE ? OR nombre LIKE ?";
                const likeTerm = `%${data.termino}%`;
                bind = [likeTerm, likeTerm];
            }
            db.exec({
                sql: sql, bind: bind,
                callback: (row) => {
                    rows.push({ idMateria: row[0], codigo: row[1], nombre: row[2], uv: row[3] });
                }
            });
            self.postMessage({ type: 'RESULTADO_BUSQUEDA_MATERIAS', data: rows });
        }

        if (type === 'ELIMINAR_MATERIA') {
            db.exec({ sql: "DELETE FROM materias WHERE idMateria = ?", bind: [data.idMateria] });
            self.postMessage({ type: 'SUCCESS_ELIMINAR_MATERIA' });
        }

        // ==========================================
        // MÓDULO DOCENTES (Corregido)
        // ==========================================
        if (type === 'GUARDAR_DOCENTE') {
            db.exec({
                sql: `INSERT OR REPLACE INTO docentes VALUES (?,?,?,?,?,?,?)`,
                bind: [data.idDocente, data.codigo, data.nombre, data.direccion, data.email, data.telefono, data.escalafon]
            });
            self.postMessage({ type: 'SUCCESS_DOCENTE' });
        }

        if (type === 'BUSCAR_DOCENTES') {
            const rows = [];
            let sql = "SELECT * FROM docentes";
            let bind = [];
            if (data && data.termino) {
                sql += " WHERE codigo LIKE ? OR nombre LIKE ?";
                const term = `%${data.termino}%`;
                bind = [term, term];
            }
            db.exec({
                sql: sql, bind: bind,
                callback: (row) => {
                    rows.push({ idDocente: row[0], codigo: row[1], nombre: row[2], direccion: row[3], email: row[4], telefono: row[5], escalafon: row[6] });
                }
            });
            self.postMessage({ type: 'RESULTADO_BUSQUEDA_DOCENTES', data: rows });
        }

        if (type === 'ELIMINAR_DOCENTE') {
            db.exec({ sql: "DELETE FROM docentes WHERE idDocente = ?", bind: [data.idDocente] });
            self.postMessage({ type: 'SUCCESS_ELIMINAR_DOCENTE' });
        }

        // ==========================================
        // MÓDULO MATRÍCULAS
        // ==========================================
        if (type === 'OBTENER_LISTA_ALUMNOS') {
            const rows = [];
            db.exec({
                sql: "SELECT idAlumno, codigo, nombre FROM alumnos ORDER BY nombre",
                callback: (row) => rows.push({ idAlumno: row[0], codigo: row[1], nombre: row[2] })
            });
            self.postMessage({ type: 'RESULTADO_LISTA_ALUMNOS', data: rows });
        }

        if (type === 'GUARDAR_MATRICULA') {
            db.exec({
                sql: `INSERT OR REPLACE INTO matriculas VALUES (?,?,?,?)`,
                bind: [data.idMatricula, data.idAlumno, data.fecha, data.ciclo]
            });
            self.postMessage({ type: 'SUCCESS_GUARDAR_MATRICULA' });
        }

        if (type === 'BUSCAR_MATRICULAS_COMPLETAS') {
            const rows = [];
            let sql = `
                SELECT m.idMatricula, m.idAlumno, m.fecha, m.ciclo, 
                       a.codigo as codigoAlumno, a.nombre as nombreAlumno 
                FROM matriculas m
                LEFT JOIN alumnos a ON m.idAlumno = a.idAlumno
            `;
            let bind = [];
            if (data && data.termino) {
                sql += " WHERE a.codigo LIKE ? OR a.nombre LIKE ? OR m.ciclo LIKE ?";
                const likeTerm = `%${data.termino}%`;
                bind = [likeTerm, likeTerm, likeTerm];
            }
            db.exec({
                sql: sql, bind: bind,
                callback: (row) => {
                    rows.push({ 
                        idMatricula: row[0], idAlumno: row[1], fecha: row[2], ciclo: row[3],
                        codigoAlumno: row[4] || '---', nombreAlumno: row[5] || '---'
                    });
                }
            });
            self.postMessage({ type: 'RESULTADO_BUSQUEDA_MATRICULAS', data: rows });
        }

        if (type === 'ELIMINAR_MATRICULA') {
            db.exec({ sql: "DELETE FROM matriculas WHERE idMatricula = ?", bind: [data.idMatricula] });
            self.postMessage({ type: 'SUCCESS_ELIMINAR_MATRICULA' });
        }

        // ==========================================
        // MÓDULO INSCRIPCIONES
        // ==========================================
        if (type === 'OBTENER_LISTA_MATERIAS') {
            const rows = [];
            db.exec({
                sql: "SELECT idMateria, codigo, nombre FROM materias ORDER BY nombre",
                callback: (row) => rows.push({ idMateria: row[0], codigo: row[1], nombre: row[2] })
            });
            self.postMessage({ type: 'RESULTADO_LISTA_MATERIAS', data: rows });
        }

        if (type === 'VALIDAR_MATRICULA') {
            let count = 0;
            db.exec({
                sql: "SELECT COUNT(*) FROM matriculas WHERE idAlumno = ?",
                bind: [data.idAlumno],
                callback: (row) => { count = row[0]; }
            });
            self.postMessage({ type: 'RESULTADO_VALIDAR_MATRICULA', estaMatriculado: count > 0 });
        }

        if (type === 'GUARDAR_INSCRIPCION') {
            db.exec({
                sql: `INSERT OR REPLACE INTO inscripciones VALUES (?,?,?,?)`,
                bind: [data.idInscripcion, data.idAlumno, data.idMateria, data.fecha]
            });
            self.postMessage({ type: 'SUCCESS_GUARDAR_INSCRIPCION' });
        }

        if (type === 'BUSCAR_INSCRIPCIONES_COMPLETAS') {
            const rows = [];
            let sql = `
                SELECT i.idInscripcion, i.fecha, i.idAlumno, i.idMateria,
                       a.codigo as codigoAlumno, a.nombre as nombreAlumno,
                       m.codigo as codigoMateria, m.nombre as nombreMateria
                FROM inscripciones i
                LEFT JOIN alumnos a ON i.idAlumno = a.idAlumno
                LEFT JOIN materias m ON i.idMateria = m.idMateria
            `;
            let bind = [];
            if (data && data.termino) {
                sql += " WHERE a.codigo LIKE ? OR a.nombre LIKE ? OR m.codigo LIKE ? OR m.nombre LIKE ?";
                const likeTerm = `%${data.termino}%`;
                bind = [likeTerm, likeTerm, likeTerm, likeTerm];
            }
            db.exec({
                sql: sql, bind: bind,
                callback: (row) => {
                    rows.push({
                        idInscripcion: row[0], fecha: row[1], idAlumno: row[2], idMateria: row[3],
                        codigoAlumno: row[4] || '---', nombreAlumno: row[5] || '---',
                        codigoMateria: row[6] || '---', nombreMateria: row[7] || '---'
                    });
                }
            });
            self.postMessage({ type: 'RESULTADO_BUSQUEDA_INSCRIPCIONES', data: rows });
        }

        if (type === 'ELIMINAR_INSCRIPCION') {
            db.exec({ sql: "DELETE FROM inscripciones WHERE idInscripcion = ?", bind: [data.idInscripcion] });
            self.postMessage({ type: 'SUCCESS_ELIMINAR_INSCRIPCION' });
        }

    } catch (err) {
        self.postMessage({ type: 'ERROR', message: err.message });
    }
};