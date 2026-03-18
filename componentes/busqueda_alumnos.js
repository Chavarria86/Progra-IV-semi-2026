/**
 * componentes/busqueda_alumnos.js
 * Componente Vue para buscar, listar y eliminar Alumnos
 * Operaciones: obtenerAlumnos (SELECT), eliminar (DELETE)
 */

'use strict';

const buscar_alumnos = {
    name: 'buscar_alumnos',
    props: ['forms'],
    emits: ['modificar'],
    template: `
    <div v-draggable class="card shadow" style="width:700px; top:80px; left:540px;">
        <div class="card-header d-flex justify-content-between align-items-center bg-secondary text-white">
            <span>📋 Listado de Alumnos</span>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" v-model="filtro"
                       placeholder="Filtrar..." style="width:180px;" @input="filtrar" />
                <button class="btn btn-sm btn-light" @click="obtenerAlumnos">🔄</button>
            </div>
        </div>
        <div class="card-body p-0" style="max-height:400px; overflow-y:auto;">
            <table class="table table-sm table-hover table-striped mb-0">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="listaFiltrada.length === 0">
                        <td colspan="6" class="text-center text-muted py-3">Sin registros</td>
                    </tr>
                    <tr v-for="(alumno, idx) in listaFiltrada" :key="alumno.id_Alumno">
                        <td>{{ idx + 1 }}</td>
                        <td>{{ alumno.codigo }}</td>
                        <td>{{ alumno.nombre }}</td>
                        <td>{{ alumno.telefono }}</td>
                        <td>{{ alumno.email }}</td>
                        <td>
                            <button class="btn btn-xs btn-warning btn-sm me-1"
                                    @click="$emit('modificar', alumno)" title="Editar">✏️</button>
                            <button class="btn btn-xs btn-danger btn-sm"
                                    @click="eliminarAlumno(alumno)" title="Eliminar">🗑️</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted small">
            Total: {{ listaFiltrada.length }} registro(s)
        </div>
    </div>
    `,
    data() {
        return {
            lista:        [],
            listaFiltrada:[],
            filtro:       ''
        };
    },
    methods: {
        async obtenerAlumnos() {
            try {
                this.lista = await dbQuery(
                    `SELECT id_Alumno, codigo, nombre, direccion, telefono, email FROM alumnos ORDER BY nombre ASC`
                );
                this.filtrar();
            } catch (e) {
                console.error(e);
                alertify.error('Error al obtener alumnos: ' + e.message);
            }
        },

        filtrar() {
            const f = this.filtro.toLowerCase().trim();
            if (!f) {
                this.listaFiltrada = [...this.lista];
                return;
            }
            this.listaFiltrada = this.lista.filter(a =>
                a.codigo.toLowerCase().includes(f)  ||
                a.nombre.toLowerCase().includes(f)  ||
                a.email.toLowerCase().includes(f)   ||
                a.telefono.toLowerCase().includes(f)
            );
        },

        eliminarAlumno(alumno) {
            alertify.confirm(
                'Eliminar Alumno',
                `¿Está seguro de eliminar al alumno <b>${alumno.nombre}</b>?`,
                async () => {
                    try {
                        await dbExec(`DELETE FROM alumnos WHERE id_Alumno = ?`, [alumno.id_Alumno]);
                        alertify.success('Alumno eliminado correctamente.');
                        await this.obtenerAlumnos();
                    } catch (e) {
                        console.error(e);
                        alertify.error('Error al eliminar alumno: ' + e.message);
                    }
                },
                () => {}
            );
        }
    }
};
