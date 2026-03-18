/**
 * componentes/docentes.js
 * Componente Vue para el formulario de registro/edición de Docentes
 */

'use strict';

const docentes = {
    name: 'docentes',
    props: ['forms'],
    emits: ['buscar'],
    template: `
    <div v-draggable class="card shadow" style="width:480px; top:80px; left:30px;">
        <div class="card-header d-flex justify-content-between align-items-center bg-warning text-dark">
            <span>👨‍🏫 Gestión de Docentes</span>
            <button class="btn btn-sm btn-dark" @click="$emit('buscar')">
                🔍 Buscar
            </button>
        </div>
        <div class="card-body">
            <form @submit.prevent>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Código</label>
                    <input type="text" class="form-control" v-model="form.codigo" placeholder="Ej: DOC-001" maxlength="25" />
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Nombre completo</label>
                    <input type="text" class="form-control" v-model="form.nombre" placeholder="Nombre y apellidos" maxlength="150" />
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Especialidad</label>
                    <input type="text" class="form-control" v-model="form.especialidad" placeholder="Ej: Matemáticas" maxlength="150" />
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Teléfono</label>
                    <input type="text" class="form-control" v-model="form.telefono" placeholder="Ej: 7777-0000" maxlength="10" />
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" v-model="form.email" placeholder="correo@ejemplo.com" maxlength="150" />
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-warning w-100" @click="guardarDocente">
                        💾 {{ form.id_Docente ? 'Actualizar' : 'Guardar' }}
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
                id_Docente:   '',
                codigo:       '',
                nombre:       '',
                especialidad: '',
                telefono:     '',
                email:        ''
            }
        };
    },
    methods: {
        limpiar() {
            this.form = { id_Docente: '', codigo: '', nombre: '', especialidad: '', telefono: '', email: '' };
        },

        validar() {
            const { codigo, nombre, telefono, email } = this.form;
            if (!codigo.trim())   { alertify.warning('El código es requerido.');   return false; }
            if (!nombre.trim())   { alertify.warning('El nombre es requerido.');   return false; }
            if (!telefono.trim()) { alertify.warning('El teléfono es requerido.'); return false; }
            if (!email.trim())    { alertify.warning('El email es requerido.');    return false; }
            return true;
        },

        async guardarDocente() {
            if (!this.validar()) return;

            try {
                if (this.form.id_Docente) {
                    await dbExec(
                        `UPDATE docentes SET codigo=?, nombre=?, especialidad=?, telefono=?, email=? WHERE id_Docente=?`,
                        [this.form.codigo, this.form.nombre, this.form.especialidad,
                         this.form.telefono, this.form.email, this.form.id_Docente]
                    );
                    alertify.success('Docente actualizado correctamente.');
                } else {
                    const id_Docente = uuid.v4();
                    await dbExec(
                        `INSERT INTO docentes (id_Docente, codigo, nombre, especialidad, telefono, email)
                         VALUES (?, ?, ?, ?, ?, ?)`,
                        [id_Docente, this.form.codigo, this.form.nombre,
                         this.form.especialidad, this.form.telefono, this.form.email]
                    );
                    alertify.success('Docente guardado correctamente.');
                }
                this.limpiar();
                this.$emit('buscar');
            } catch (e) {
                console.error(e);
                alertify.error('Error al guardar docente: ' + e.message);
            }
        },

        modificarDocente(data) {
            this.form = { ...data };
        }
    }
};
