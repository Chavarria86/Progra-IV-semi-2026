const { createApp } = Vue;

// Base de datos
const db = new Dexie("db_USSS027724");

// SOLO tablas necesarias
db.version(1).stores({
    autores: "++idAutor, codigo, nombre, nacionalidad",
    libros: "++idLibro, idAutor, titulo, isbn, genero"
});

createApp({

    components: {
        autores,
        busqueda_autores,
        libros,
        busqueda_libros
    },

    data() {
        return {
            forms: {
                autores: { mostrar: true },
                busqueda_autores: { mostrar: false },
                libros: { mostrar: false },
                busqueda_libros: { mostrar: false }
            }
        }
    },

    methods: {

        abrirVentana(ventana) {
            Object.keys(this.forms).forEach(key => {
                this.forms[key].mostrar = false;
            });

            this.forms[ventana].mostrar = true;
        },

        buscar(ventana, metodo) {
            if (this.$refs[ventana]) {
                this.$refs[ventana][metodo]();
            }
        },

        modificar(ventana, metodo, data) {
            if (this.$refs[ventana]) {
                this.$refs[ventana][metodo](data);
            }

            // Regresar al formulario después de seleccionar
            if (ventana === "autores") {
                this.forms.busqueda_autores.mostrar = false;
                this.forms.autores.mostrar = true;
            }

            if (ventana === "libros") {
                this.forms.busqueda_libros.mostrar = false;
                this.forms.libros.mostrar = true;
            }
        }
    }

}).mount("#app");