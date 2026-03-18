'use strict';

const { createApp } = Vue;

const uuid = {
    v4() {
        if (globalThis.crypto?.randomUUID) {
            return globalThis.crypto.randomUUID();
        }
        return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
    }
};

(async () => {
    try {
        await globalThis.dbReady;

        createApp({
            components: {
                alumnos,
                buscar_alumnos,
                materias,
                buscar_materias,
                docentes,
                buscar_docentes,
                matriculas,
                buscar_matriculas,
                inscripciones,
                buscar_inscripciones
            },
            data() {
                return {
                    forms: {
                        alumnos:              { mostrar: false },
                        busqueda_alumnos:     { mostrar: false },
                        materias:             { mostrar: false },
                        busqueda_materias:    { mostrar: false },
                        docentes:             { mostrar: false },
                        busqueda_docentes:    { mostrar: false },
                        matriculas:           { mostrar: false },
                        busqueda_matriculas:  { mostrar: false },
                        inscripciones:        { mostrar: false },
                        busqueda_inscripciones:{ mostrar: false }
                    }
                };
            },
            methods: {
                buscar(ventana, metodo) {
                    this.$refs[ventana][metodo]();
                },
                abrirVentana(ventana) {
                    this.forms[ventana].mostrar = !this.forms[ventana].mostrar;
                },
                modificar(ventana, metodo, data) {
                    this.$refs[ventana][metodo](data);
                }
            }
        })
        .directive('draggable', vDraggable)
        .mount('#app');

        console.log('[App] Sistema Académico iniciado correctamente con SQLite WASM.');

    } catch (error) {
        console.error('[App] Error al inicializar:', error);
        if (globalThis.alertify) {
            alertify.error('No se pudo inicializar SQLite WASM + OPFS.');
        }
    }
})();
