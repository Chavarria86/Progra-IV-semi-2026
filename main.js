const { createApp } = Vue;

createApp({
    components: {
        alumnos, busqueda_alumnos,
        materias, busqueda_materias,
        matriculas, busqueda_matriculas,
        inscripciones, busqueda_inscripciones,
        docentes, busqueda_docentes
    },
    data() {
        return {
            forms: {
                alumnos: { mostrar: false },
                busqueda_alumnos: { mostrar: false },
                materias: { mostrar: false },
                busqueda_materias: { mostrar: false },
                matriculas: { mostrar: false },
                busqueda_matriculas: { mostrar: false },
                inscripciones: { mostrar: false },
                busqueda_inscripciones: { mostrar: false },
                docentes: { mostrar: false },
                busqueda_docentes: { mostrar: false }
            }
        }
    },
    methods: {
        abrirVentana(formulario) {
            this.cerrarTodosLosFormularios();
            this.forms[formulario].mostrar = true;
        },
        cerrarTodosLosFormularios() {
            for (let form in this.forms) {
                this.forms[form].mostrar = false;
            }
        },
        buscar(form, metodo) {
            this.forms[form].mostrar = true;
            this.$refs[form][metodo]();
        },
        modificar(form, metodo, datos) {
            this.forms[form].mostrar = true;
            this.$refs[form][metodo](datos);
        }
    }
}).mount('#app');