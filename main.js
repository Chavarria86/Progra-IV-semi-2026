const { createApp } = Vue;
const Dexie = window.Dexie;
const sha256 = window.sha256;


const db = new Dexie("db_USSS027724");

db.version(1).stores({
    autor: "idAutor, codigo, nombre, pais, telefono",
    libros: "idLibro, idAutor, isbn, titulo, editorial, edicion"
});


createApp({
    components: {
        autor,
        busqueda_autor,
        libros,
        busqueda_libros
    },
    data() {
        return {
            forms: {
                autor: { mostrar: false },
                busqueda_autor: { mostrar: false },
                libros: { mostrar: false },
                busqueda_libros: { mostrar: false }
            }
        }
    },
    methods: {
        buscar(ventana, metodo) {
            this.$refs[ventana][metodo]();
        },
        abrirVentana(ventana) {
            this.forms[ventana].mostrar = !this.forms[ventana].mostrar;
        },
        modificar(ventana, metodo, data) {
            this.forms[ventana].mostrar = true;
            this.forms['busqueda_' + ventana].mostrar = false;
            this.$refs[ventana][metodo](data);
        }
    }
}).mount("#app");