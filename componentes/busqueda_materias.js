/**
 * componentes/busqueda_materias.js
 * Componente Vue para buscar, listar y eliminar Materias
 */

'use strict';

const buscar_materias = {
    name: 'buscar_materias',
    props: ['forms'],
    emits: ['modificar'],
    template: `
    <div v-draggable class="card shadow" style="width:680px; top:80px; left:540px;">
        <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
            <span>📋 Listado de Materias</span>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" v-model="filtro"
                       placeholder="Filtrar..." style="width:180px;" @input="filtrar" />
                <button class="btn btn-sm btn-light" @click="obtenerMaterias">🔄</button>
            </div>
        </div>
        <div class="card-body p-0" style="max-height:400px; overflow-y:auto;">
            <table class="table table-sm table-hover table-striped mb-0">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Créditos</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="listaFiltrada.length === 0">
                        <td colspan="6" class="text-center text-muted py-3">Sin registros</td>
                    </tr>
                    <tr v-for="(materia, idx) in listaFiltrada" :key="materia.id_Materia">
                        <td>{{ idx + 1 }}</td>
                        <td>{{ materia.codigo }}</td>
                        <td>{{ materia.nombre }}</td>
                        <td class="text-center">{{ materia.creditos }}</td>
                        <td>{{ materia.descripcion }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-1"
                                    @click="$emit('modificar', materia)" title="Editar">✏️</button>
                            <button class="btn btn-danger btn-sm"
                                    @click="eliminarMateria(materia)" title="Eliminar">🗑️</button>
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
        async obtenerMaterias() {
            try {
                this.lista = await dbQuery(
                    `SELECT id_Materia, codigo, nombre, creditos, descripcion FROM materias ORDER BY nombre ASC`
                );
                this.filtrar();
            } catch (e) {
                console.error(e);
                alertify.error('Error al obtener materias: ' + e.message);
            }
        },

        filtrar() {
            const f = this.filtro.toLowerCase().trim();
            if (!f) {
                this.listaFiltrada = [...this.lista];
                return;
            }
            this.listaFiltrada = this.lista.filter(m =>
                m.codigo.toLowerCase().includes(f) ||
                m.nombre.toLowerCase().includes(f) ||
                (m.descripcion || '').toLowerCase().includes(f)
            );
        },

        eliminarMateria(materia) {
            alertify.confirm(
                'Eliminar Materia',
                `¿Está seguro de eliminar la materia <b>${materia.nombre}</b>?`,
                async () => {
                    try {
                        await dbExec(`DELETE FROM materias WHERE id_Materia = ?`, [materia.id_Materia]);
                        alertify.success('Materia eliminada correctamente.');
                        await this.obtenerMaterias();
                    } catch (e) {
                        console.error(e);
                        alertify.error('Error al eliminar materia: ' + e.message);
                    }
                },
                () => {}
            );
        }
    }
};
