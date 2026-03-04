const busqueda_autor = {
    data() {
        return {
            buscar: '',
            autores: []
        }
    },
    methods: {
        modificarAutor(autor) {
            this.$emit('modificar', autor);
        },
        async obtenerAutores() {
            this.autores = await db.autor.filter(
                autor => autor.codigo.toLowerCase().includes(this.buscar.toLowerCase())
                    || autor.nombre.toLowerCase().includes(this.buscar.toLowerCase())
                    || autor.pais.toLowerCase().includes(this.buscar.toLowerCase())
                    || autor.telefono.toLowerCase().includes(this.buscar.toLowerCase())
            ).toArray();
        },
        async eliminarAutor(autor, e) {
            e.stopPropagation();
            alertify.confirm('Eliminar Autor', `¿Está seguro de eliminar el autor ${autor.nombre}?`, async e => {
                await db.autor.delete(autor.idAutor);
                this.obtenerAutores();
                alertify.success(`Autor ${autor.nombre} eliminado correctamente`);
            }, () => {
                //No hacer nada
            });
        },
    },
    template: `
        <div class="row justify-content-center mt-3">
            <div class="col-12 col-md-11">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0 text-center"><i class="bi bi-search me-2"></i>BÚSQUEDA DE AUTORES</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3 bg-light border-bottom">
                            <input autocomplete="off" type="search" @keyup="obtenerAutores()" v-model="buscar" placeholder="🔍 Buscar autor (código, nombre, país o teléfono)" class="form-control form-control-lg shadow-sm border-0">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 text-center align-middle" id="tblAutores">
                                <thead class="table-dark">
                                    <tr>
                                        <th>CÓDIGO</th>
                                        <th>NOMBRE</th>
                                        <th>PAÍS</th>
                                        <th>TELÉFONO</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="autores.length === 0">
                                        <td colspan="5" class="py-4 text-muted">No se encontraron autores. Intenta realizar una búsqueda.</td>
                                    </tr>
                                    <tr v-for="autor in autores" :key="autor.idAutor" @click="modificarAutor(autor)" style="cursor: pointer;" class="transition-hover">
                                        <td class="fw-bold">{{ autor.codigo }}</td>
                                        <td>{{ autor.nombre }}</td>
                                        <td>{{ autor.pais }}</td>
                                        <td>{{ autor.telefono }}</td>
                                        <td>
                                            <button class="btn btn-warning text-white btn-sm px-3 shadow-sm rounded-pill me-2" @click.stop="modificarAutor(autor)">
                                                <i class="bi bi-pencil-square"></i> EDITAR
                                            </button>
                                            <button class="btn btn-danger btn-sm px-3 shadow-sm rounded-pill" @click="eliminarAutor(autor, $event)">
                                                <i class="bi bi-trash"></i> ELIMINAR
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `
};