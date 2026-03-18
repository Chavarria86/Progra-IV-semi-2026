/**
 * componentes/materias.js
 * Componente Vue para el formulario de registro/edición de Materias
 */

'use strict';

const materias = {
    name: 'materias',
    props: ['forms'],
    emits: ['buscar'],
    template: `
    <div v-draggable class="card shadow" style="width:480px; top:80px; left:30px;">
        <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
            <span>📚 Gestión de Materias</span>
            <button class="btn btn-sm btn-light" @click="$emit('buscar')">
                🔍 Buscar
            </button>
        </div>
        <div class="card-body">
            <form @submit.prevent>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Código</label>
                    <input type="text" class="form-control" v-model="form.codigo" placeholder="Ej: MAT-001" maxlength="25" />
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Nombre</label>
                    <input type="text" class="form-control" v-model="form.nombre" placeholder="Nombre de la materia" maxlength="150" />
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Créditos</label>
                    <input type="number" class="form-control" v-model.number="form.creditos" placeholder="Número de créditos" min="0" max="20" />
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Descripción</label>
                    <textarea class="form-control" v-model="form.descripcion" rows="2" placeholder="Descripción breve"></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success w-100" @click="guardarMateria">
                        💾 {{ form.id_Materia ? 'Actualizar' : 'Guardar' }}
                    </button>
                    <button class="btn btn-secondary w-100" @click="limpiar">
                        🗑️ Limpiar
                    </button>
                </div>
            </form>
        </div>
    </div>
    `,
    data() {
        return {
            form: {
                id_Materia:  '',
                codigo:      '',
                nombre:      '',
                creditos:    0,
                descripcion: ''
            }
        };
    },
    methods: {
        limpiar() {
            this.form = { id_Materia: '', codigo: '', nombre: '', creditos: 0, descripcion: '' };
        },

        validar() {
            const { codigo, nombre } = this.form;
            if (!codigo.trim()) { alertify.warning('El código es requerido.'); return false; }
            if (!nombre.trim()) { alertify.warning('El nombre es requerido.'); return false; }
            return true;
        },

        async guardarMateria() {
            if (!this.validar()) return;

            try {
                if (this.form.id_Materia) {
                    await dbExec(
                        `UPDATE materias SET codigo=?, nombre=?, creditos=?, descripcion=? WHERE id_Materia=?`,
                        [this.form.codigo, this.form.nombre, this.form.creditos,
                         this.form.descripcion, this.form.id_Materia]
                    );
                    alertify.success('Materia actualizada correctamente.');
                } else {
                    const id_Materia = uuid.v4();
                    await dbExec(
                        `INSERT INTO materias (id_Materia, codigo, nombre, creditos, descripcion)
                         VALUES (?, ?, ?, ?, ?)`,
                        [id_Materia, this.form.codigo, this.form.nombre,
                         this.form.creditos, this.form.descripcion]
                    );
                    alertify.success('Materia guardada correctamente.');
                }
                this.limpiar();
                this.$emit('buscar');
            } catch (e) {
                console.error(e);
                alertify.error('Error al guardar materia: ' + e.message);
            }
        },

        modificarMateria(data) {
            this.form = { ...data };
        }
    }
};
