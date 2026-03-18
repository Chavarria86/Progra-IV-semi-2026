/**
 * componentes/busqueda_matriculas.js
 * Componente Vue para buscar, listar y eliminar Matrículas
 */

'use strict';

const buscar_matriculas = {
    name: 'buscar_matriculas',
    props: ['forms'],
    emits: ['modificar'],
    template: `
    <div v-draggable class="card shadow" style="width:720px; top:80px; left:560px;">
        <div class="card-header d-flex justify-content-between align-items-center bg-info text-white">
            <span>📋 Listado de Matrículas</span>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" v-model="filtro"
                       placeholder="Filtrar..." style="width:180px;" @input="filtrar" />
                <button class="btn btn-sm btn-light" @click="obtenerMatriculas">🔄</button>
            </div>
        </div>
        <div class="card-body p-0" style="max-height:400px; overflow-y:auto;">
            <table class="table table-sm table-hover table-striped mb-0">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Alumno</th>
                        <th>Ciclo</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="listaFiltrada.length === 0">
                        <td colspan="5" class="text-center text-muted py-3">Sin registros</td>
                    </tr>
                    <tr v-for="(m, idx) in listaFiltrada" :key="m.id_Matricula">
                        <td>{{ idx + 1 }}</td>
                        <td>{{ m.nombre_alumno }}</td>
                        <td>{{ m.ciclo }}</td>
                        <td>{{ m.fecha }}</td>
                        <td>
                            <button class="btn btn-info btn-sm text-white me-1"
                                    @click="$emit('modificar', m)" title="Editar">✏️</button>
                            <button class="btn btn-danger btn-sm"
                                    @click="eliminarMatricula(m)" title="Eliminar">🗑️</button>
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
        async obtenerMatriculas() {
            try {
                this.lista = await dbQuery(`
                    SELECT m.id_Matricula, m.id_Alumno, m.ciclo, m.fecha,
                           a.nombre AS nombre_alumno, a.codigo AS codigo_alumno
                    FROM matriculas m
                    INNER JOIN alumnos a ON m.id_Alumno = a.id_Alumno
                    ORDER BY m.fecha DESC, a.nombre ASC
                `);
                this.filtrar();
            } catch (e) {
                console.error(e);
                alertify.error('Error al obtener matrículas: ' + e.message);
            }
        },

        filtrar() {
            const f = this.filtro.toLowerCase().trim();
            if (!f) {
                this.listaFiltrada = [...this.lista];
                return;
            }
            this.listaFiltrada = this.lista.filter(m =>
                m.nombre_alumno.toLowerCase().includes(f) ||
                m.ciclo.toLowerCase().includes(f)         ||
                m.fecha.toLowerCase().includes(f)
            );
        },

        eliminarMatricula(m) {
            alertify.confirm(
                'Eliminar Matrícula',
                `¿Está seguro de eliminar la matrícula de <b>${m.nombre_alumno}</b> (${m.ciclo})?`,
                async () => {
                    try {
                        await dbExec(`DELETE FROM matriculas WHERE id_Matricula = ?`, [m.id_Matricula]);
                        alertify.success('Matrícula eliminada correctamente.');
                        await this.obtenerMatriculas();
                    } catch (e) {
                        console.error(e);
                        alertify.error('Error al eliminar matrícula: ' + e.message);
                    }
                },
                () => {}
            );
        }
    }
};
