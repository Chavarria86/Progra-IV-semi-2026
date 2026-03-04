const autor = {
    props: ['forms'],

    data() {
        return {
            modoEdicion: false,
            autor: {
                idAutor: null,
                codigo: "",
                nombre: "",
                pais: "",
                telefono: ""
            }
        }
    },

    computed: {
        tituloFormulario() {
            return this.modoEdicion ? "Actualizar Autor" : "Registrar Autor";
        }
    },

    methods: {

        toggleBusqueda() {
            this.forms.busqueda_autor.mostrar = !this.forms.busqueda_autor.mostrar;
            this.$emit('buscar');
        },

        cargarAutor(datos) {
            this.modoEdicion = true;
            this.autor = { ...datos };
        },

        async guardarAutor() {

            if (!this.autor.codigo.trim()) {
                alertify.error("El código es obligatorio");
                return;
            }

            const existe = await db.autor
                .where('codigo')
                .equals(this.autor.codigo)
                .toArray();

            if (existe.length && !this.modoEdicion) {
                alertify.warning("Ese código ya existe");
                return;
            }

            this.autor.idAutor = this.modoEdicion
                ? this.autor.idAutor
                : Date.now();

            await db.autor.put(this.autor);

            alertify.success("Autor guardado correctamente");
            this.resetFormulario();
        },

        resetFormulario() {
            this.modoEdicion = false;
            this.autor = {
                idAutor: null,
                codigo: "",
                nombre: "",
                pais: "",
                telefono: ""
            };
        }
    },

    template: `
        <section class="container my-4">
            <div class="p-4 rounded shadow" style="background:#1f2937; border:1px solid #374151;">
                
                <h4 class="text-light text-center mb-4">
                    {{ tituloFormulario }}
                </h4>

                <form @submit.prevent="guardarAutor">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label text-light">codigo</label>
                            <input v-model="autor.codigo"
                                type="text"
                                class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-light">nombre</label>
                            <input v-model="autor.nombre"
                                type="text"
                                class="form-control form-control-lg">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-light">pais</label>
                            <input v-model="autor.pais"
                                type="text"
                                class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-light">telefono</label>
                            <input v-model="autor.telefono"
                                type="text"
                                class="form-control form-control-sm">
                        </div>

                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <div>
                            <button class="btn btn-outline-light">
                                {{ modoEdicion ? "Actualizar" : "Guardar" }}
                            </button>

                            <button type="button"
                                @click="resetFormulario"
                                class="btn btn-secondary ms-2">
                                Limpiar
                            </button>
                        </div>

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