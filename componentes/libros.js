const libros = {
    props: ['forms'],

    data() {
        return {
            modoEdicion: false,
            libro: {
                idLibro: null,
                idAutor: "",
                isbn: "",
                titulo: "",
                editorial: "",
                edicion: ""
            },
            autores: []
        }
    },

    methods: {

        toggleBusqueda() {
            this.forms.busqueda_libros.mostrar = !this.forms.busqueda_libros.mostrar;
            this.$emit('buscar');
        },

        cargarLibro(datos) {
            this.modoEdicion = true;
            this.libro = { ...datos };
        },

        async guardarLibro() {
            try {
                if (!this.libro.isbn.trim()) {
                    alertify.error("El ISBN es obligatorio");
                    return;
                }

                const existe = await db.libros
                    .where('isbn')
                    .equals(this.libro.isbn)
                    .toArray();

                if (existe.length && !this.modoEdicion) {
                    alertify.warning("Ese ISBN ya existe");
                    return;
                }

                this.libro.idLibro = this.modoEdicion
                    ? this.libro.idLibro
                    : Date.now();

                // Unwrap the Vue Proxy to avoid DataCloneError in Dexie
                await db.libros.put(JSON.parse(JSON.stringify(this.libro)));

                alertify.success("Libro guardado correctamente");
                this.resetFormulario();
            } catch (error) {
                console.error("Error al guardar libro:", error);
                alertify.error("Error al guardar libro observando la consola.");
            }
        },

        resetFormulario() {
            this.modoEdicion = false;
            this.libro = {
                idLibro: null,
                idAutor: "",
                isbn: "",
                titulo: "",
                editorial: "",
                edicion: ""
            };
        },

        async cargarAutores() {
            this.autores = await db.autor.toArray();
        }
    },

    mounted() {
        this.cargarAutores();
    },

    template: `
        <section class="container my-4">
            <div class="p-4 rounded shadow" style="background:#1f2937; border:1px solid #374151;">
                
                <h4 class="text-light text-center mb-4">
                    {{ modoEdicion ? "Actualizar Libro" : "Registrar Libro" }}
                </h4>

                <form @submit.prevent="guardarLibro">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label text-light">isbn</label>
                            <input v-model="libro.isbn"
                                type="text"
                                class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-light">titulo</label>
                            <input v-model="libro.titulo"
                                type="text"
                                class="form-control form-control-lg">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-light">editorial</label>
                            <input v-model="libro.editorial"
                                type="text"
                                class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-light">edicion</label>
                            <input v-model="libro.edicion"
                                type="text"
                                class="form-control form-control-sm">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-light">idAutor</label>
                            <select v-model="libro.idAutor"
                                class="form-select">
                                <option disabled value="">Seleccione autor</option>
                                <option v-for="a in autores"
                                    :key="a.idAutor"
                                    :value="a.idAutor">
                                    {{ a.nombre }}
                                </option>
                            </select>
                        </div>

                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-outline-light">
                            {{ modoEdicion ? "Actualizar" : "Guardar" }}
                        </button>

                        <button type="button"
                            @click="toggleBusqueda"
                            class="btn btn-success">
                            Buscar
                        </button>
                    </div>

                </form>
            </div>
        </section>
    `
};