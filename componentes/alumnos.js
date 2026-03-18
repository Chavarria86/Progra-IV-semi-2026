/**
 * componentes/alumnos.js
 * Componente Vue para el formulario de registro/edición de Alumnos
 * Operaciones: guardarAlumno, modificarAlumno
 * Persistencia: SQLite WASM (OPFS o memoria)
 */

'use strict';

const alumnos = {
    name: 'alumnos',
    props: ['forms'],
    emits: ['buscar'],
    template: `
    <div v-draggable class="card shadow" style="width:480px; top:80px; left:30px;">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <span><i class="bi bi-mortarboard-fill me-2"></i>Gestión de Alumnos</span>
            <button class="btn btn-sm btn-light" @click="$emit('buscar')">
                🔍 Buscar
            </button>
        </div>
        <div class="card-body">
            <form @submit.prevent>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Código</label>
                    <input type="text" class="form-control" v-model="form.codigo" placeholder="Ej: ALU-001" maxlength="25" />
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Nombre completo</label>
                    <input type="text" class="form-control" v-model="form.nombre" placeholder="Nombre y apellidos" maxlength="150" />
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Dirección</label>
                    <input type="text" class="form-control" v-model="form.direccion" placeholder="Dirección" maxlength="150" />
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
                    <button class="btn btn-success w-100" @click="guardarAlumno">
                        💾 {{ form.id_Alumno ? 'Actualizar' : 'Guardar' }}
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
                id_Alumno: '',
                codigo:    '',
                nombre:    '',
                direccion: '',
                telefono:  '',
                email:     ''
            }
        };
    },
    methods: {
        limpiar() {
            this.form = { id_Alumno: '', codigo: '', nombre: '', direccion: '', telefono: '', email: '' };
        },

        validar() {
            const { codigo, nombre, direccion, telefono, email } = this.form;
            if (!codigo.trim())    { alertify.warning('El código es requerido.');    return false; }
            if (!nombre.trim())    { alertify.warning('El nombre es requerido.');    return false; }
            if (!direccion.trim()) { alertify.warning('La dirección es requerida.'); return false; }
            if (!telefono.trim())  { alertify.warning('El teléfono es requerido.');  return false; }
            if (!email.trim())     { alertify.warning('El email es requerido.');     return false; }
            return true;
        },

        async guardarAlumno() {
            if (!this.validar()) return;

            try {
                if (this.form.id_Alumno) {
                    // UPDATE
                    await dbExec(
                        `UPDATE alumnos SET codigo=?, nombre=?, direccion=?, telefono=?, email=? WHERE id_Alumno=?`,
                        [this.form.codigo, this.form.nombre, this.form.direccion,
                         this.form.telefono, this.form.email, this.form.id_Alumno]
                    );
                    alertify.success('Alumno actualizado correctamente.');
                } else {
                    // INSERT
                    const id_Alumno = uuid.v4();
                    await dbExec(
                        `INSERT INTO alumnos (id_Alumno, codigo, nombre, direccion, telefono, email)
                         VALUES (?, ?, ?, ?, ?, ?)`,
                        [id_Alumno, this.form.codigo, this.form.nombre,
                         this.form.direccion, this.form.telefono, this.form.email]
                    );
                    alertify.success('Alumno guardado correctamente.');
                }
                this.limpiar();
                this.$emit('buscar');
            } catch (e) {
                console.error(e);
                alertify.error('Error al guardar alumno: ' + e.message);
            }
        },

        modificarAlumno(data) {
            this.form = { ...data };
        }
    }
};
