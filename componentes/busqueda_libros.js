const busqueda_libros = {
    data() {
        return {
            buscar: '',
            libros: []
        }
    },
    methods: {
        modificarLibro(libro) {
            this.$emit('modificar', libro);
        },
        async obtenerLibros() {
            this.libros = await db.libros.filter(
                libro => libro.isbn.toLowerCase().includes(this.buscar.toLowerCase())
                    || libro.titulo.toLowerCase().includes(this.buscar.toLowerCase())
                    || libro.editorial.toLowerCase().includes(this.buscar.toLowerCase())
                    || libro.edicion.toLowerCase().includes(this.buscar.toLowerCase())
            ).toArray();

            // Mapear nombre del autor para visualización
            for (let i = 0; i < this.libros.length; i++) {
                let autor = await db.autor.get(this.libros[i].idAutor);
                if (autor) {
                    this.libros[i].nombre_autor = autor.nombre;
                } else {
                    this.libros[i].nombre_autor = 'Desconocido';
                }
            }
        },
        async eliminarLibro(libro, e) {
            e.stopPropagation();
            alertify.confirm('Eliminar Libro', `¿Está seguro de eliminar el libro ${libro.titulo}?`, async e => {
                await db.libros.delete(libro.idLibro);
                this.obtenerLibros();
                alertify.success(`Libro ${libro.titulo} eliminado correctamente`);
            }, () => {
                //No hacer nada
            });
        },
    },
    template: `
        <div class="row justify-content-center mt-3">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0 text-center"><i class="bi bi-search me-2"></i>BÚSQUEDA DE LIBROS</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3 bg-light border-bottom">
                            <input autocomplete="off" type="search" @keyup="obtenerLibros()" v-model="buscar" placeholder="🔍 Buscar libro (ISBN, Título o Editorial)..." class="form-control form-control-lg shadow-sm border-0">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 text-center align-middle" id="tblLibros">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ISBN</th>
                                        <th>TÍTULO</th>
                                        <th>AUTOR</th>
                                        <th>EDITORIAL</th>
                                        <th>EDICIÓN</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="libros.length === 0">
                                        <td colspan="6" class="py-4 text-muted">No se encontraron libros. Intenta realizar una búsqueda.</td>
                                    </tr>
                                    <tr v-for="libro in libros" :key="libro.idLibro" @click="modificarLibro(libro)" style="cursor: pointer;" class="transition-hover">
                                        <td class="fw-bold">{{ libro.isbn }}</td>
                                        <td>{{ libro.titulo }}</td>
                                        <td><span class="badge bg-info text-dark">{{ libro.nombre_autor }}</span></td>
                                        <td>{{ libro.editorial }}</td>
                                        <td>{{ libro.edicion }}</td>
                                        <td>
                                            <button class="btn btn-warning text-white btn-sm px-3 shadow-sm rounded-pill me-2" @click.stop="modificarLibro(libro)">
                                                <i class="bi bi-pencil-square"></i> EDITAR
                                            </button>
                                            <button class="btn btn-danger btn-sm px-3 shadow-sm rounded-pill" @click="eliminarLibro(libro, $event)">
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