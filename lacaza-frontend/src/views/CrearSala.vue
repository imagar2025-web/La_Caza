<script setup>
// Vista para crear una sala nueva: el usuario elige nombre, temática y hábitos.

import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { crearSala } from '../services/api'

const router = useRouter()

// Plantillas de hábitos por temática
const plantillasTematica = {
  deporte: [
    { icono: '🏋', nombre: 'Entrenar 1 hora',       descripcion: 'Fuerza',     puntos: 5 },
    { icono: '👟', nombre: 'Caminar 10k pasos',    descripcion: 'Cardio',     puntos: 3 },
    { icono: '💧', nombre: 'Beber 3 L de agua',    descripcion: 'Hidratación', puntos: 2 },
    { icono: '🥩', nombre: 'Proteína cada comida', descripcion: 'Nutrición',  puntos: 3 },
    { icono: '🌙', nombre: 'Dormir 8 horas',        descripcion: 'Descanso',   puntos: 4 },
  ],
  estudio: [
    { icono: '📖', nombre: 'Estudiar 2 h',          descripcion: 'Deep work',   puntos: 5 },
    { icono: '📝', nombre: 'Repasar apuntes',      descripcion: 'Retención',  puntos: 3 },
    { icono: '🧠', nombre: 'Pomodoro × 4',         descripcion: 'Enfoque',    puntos: 4 },
    { icono: '💤', nombre: 'Dormir 8 horas',        descripcion: 'Memoria',    puntos: 4 },
  ],
  bienestar: [
    { icono: '🧘', nombre: 'Meditar 20 min',       descripcion: 'Paz mental', puntos: 5 },
    { icono: '✒',  nombre: 'Journaling',           descripcion: 'Claridad',   puntos: 4 },
    { icono: '☀',  nombre: 'Sol 15 min',            descripcion: 'Ánimo',      puntos: 2 },
    { icono: '🥗', nombre: 'Comida limpia',        descripcion: 'Salud',      puntos: 4 },
  ],
  trabajo: [
    { icono: '🎯', nombre: 'MIT del día',           descripcion: 'Tarea crítica', puntos: 5 },
    { icono: '📧', nombre: 'Inbox zero',            descripcion: 'Orden',      puntos: 3 },
    { icono: '🧠', nombre: 'Deep work 2 h',         descripcion: 'Enfoque',    puntos: 5 },
  ],
  custom: [
    { icono: '⭐', nombre: 'Mi hábito 1', descripcion: '', puntos: 3 },
  ],
}

const tematicas = [
  { id: 'deporte',   nombre: 'Deportista',    icono: '🏋' },
  { id: 'estudio',   nombre: 'Estudiante',    icono: '📚' },
  { id: 'bienestar', nombre: 'Bienestar',     icono: '🧘' },
  { id: 'trabajo',   nombre: 'Productividad', icono: '💼' },
  { id: 'custom',    nombre: 'Personalizada', icono: '✨' },
]

// Estado del formulario
const nombreSala = ref('')
const tematicaSeleccionada = ref(null)
const habitos = ref([])
const duracionDias = ref(30)
const maxJugadores = ref(6)
const cargando = ref(false)
const error = ref('')

// Cuando el usuario elige una temática, copiamos sus hábitos por defecto
function elegirTematica(idTematica) {
  tematicaSeleccionada.value = idTematica
  // map crea una copia para que el usuario pueda editar sin romper la plantilla
  habitos.value = plantillasTematica[idTematica].map(h => ({ ...h }))
}

function anadirHabito() {
  if (habitos.value.length >= 15) {
    alert('Máximo 15 hábitos')
    return
  }
  habitos.value.push({ icono: '⭐', nombre: '', descripcion: '', puntos: 3 })
}

function eliminarHabito(indice) {
  habitos.value.splice(indice, 1)
}

async function fundarSala() {
  error.value = ''

  if (!nombreSala.value.trim()) {
    error.value = 'Ponle un nombre a tu sala'
    return
  }
  if (!tematicaSeleccionada.value) {
    error.value = 'Elige una temática'
    return
  }
  // Filtramos hábitos vacíos
  const habitosValidos = habitos.value.filter(h => h.nombre.trim() !== '')
  if (habitosValidos.length === 0) {
    error.value = 'Añade al menos un hábito con nombre'
    return
  }

  cargando.value = true
  try {
    const respuesta = await crearSala({
      nombre: nombreSala.value,
      tematica: tematicaSeleccionada.value,
      duracionDias: duracionDias.value,
      maxJugadores: maxJugadores.value,
      habitos: habitosValidos,
    })
    // Vamos directamente a la sala recién creada
    router.push(`/sala/${respuesta.data.sala.idSala}`)
  } catch (e) {
    error.value = e.response?.data?.mensaje || 'Error al crear la sala'
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <div class="contenedor">
    <div class="panel">
      <div class="cabecera-formulario">
        <h2 class="titulo-vaquero">Nueva cacería</h2>
        <button class="boton" @click="router.push('/')">← Volver</button>
      </div>

      <!-- Nombre -->
      <div class="campo">
        <label>Nombre de la sala</label>
        <input type="text" v-model="nombreSala" placeholder="Ej: Los Gymbros de Octubre" maxlength="50" />
      </div>

      <!-- Temática -->
      <div class="campo">
        <label>Temática de la caza</label>
        <div class="rejilla-tematicas">
          <div
            v-for="t in tematicas"
            :key="t.id"
            class="tarjeta-tematica"
            :class="{ seleccionada: tematicaSeleccionada === t.id }"
            @click="elegirTematica(t.id)"
          >
            <div class="icono">{{ t.icono }}</div>
            <div class="nombre-tematica">{{ t.nombre }}</div>
          </div>
        </div>
      </div>

      <!-- Hábitos (solo si hay temática elegida) -->
      <div v-if="tematicaSeleccionada" class="campo">
        <label>Hábitos y puntuaciones</label>
        <p class="ayuda">Tú decides qué hábitos se miden y cuántos puntos valen (1-10).</p>

        <div
          v-for="(habito, indice) in habitos"
          :key="indice"
          class="fila-habito"
        >
          <input
            type="text"
            v-model="habito.icono"
            class="campo-icono"
            maxlength="2"
            placeholder="⭐"
          />
          <input
            type="text"
            v-model="habito.nombre"
            placeholder="Nombre del hábito"
            maxlength="50"
          />
          <input
            type="number"
            v-model.number="habito.puntos"
            min="1"
            max="10"
            step="0.5"
            class="campo-puntos"
          />
          <button class="boton-eliminar" @click="eliminarHabito(indice)">×</button>
        </div>

        <button class="boton" @click="anadirHabito" style="margin-top:8px">
          + Añadir hábito
        </button>
      </div>

      <!-- Duración -->
      <div class="campo">
        <label>Duración de la partida</label>
        <select v-model.number="duracionDias">
          <option :value="7">1 semana</option>
          <option :value="30">1 mes (recomendado)</option>
          <option :value="90">3 meses</option>
        </select>
      </div>

      <!-- Máx jugadores -->
      <div class="campo">
        <label>Máximo de jugadores</label>
        <input type="number" v-model.number="maxJugadores" min="2" max="20" />
      </div>

      <p v-if="error" class="error">{{ error }}</p>

      <div class="botones-finales">
        <button class="boton" @click="router.push('/')">Cancelar</button>
        <button class="boton principal" @click="fundarSala" :disabled="cargando">
          {{ cargando ? 'Creando...' : 'Fundar sala' }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.cabecera-formulario {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  padding-bottom: 10px;
  border-bottom: 2px solid var(--color-tinta);
}
.cabecera-formulario h2 {
  font-size: 22px;
  color: var(--color-sangre);
}
.campo {
  margin-bottom: 20px;
}
.ayuda {
  font-style: italic;
  color: var(--color-tinta-suave);
  margin-bottom: 10px;
  font-size: 14px;
}

.rejilla-tematicas {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 10px;
}
.tarjeta-tematica {
  border: 2px solid var(--color-tinta);
  background: var(--color-papel);
  padding: 14px;
  text-align: center;
  cursor: pointer;
  box-shadow: 2px 2px 0 var(--color-tinta);
  transition: all 0.15s ease;
}
.tarjeta-tematica:hover {
  background: var(--color-oro-brillo);
}
.tarjeta-tematica.seleccionada {
  background: var(--color-tinta);
  color: var(--color-papel);
  box-shadow: 3px 3px 0 var(--color-sangre);
  transform: translate(-1px, -1px);
}
.tarjeta-tematica .icono {
  font-size: 28px;
  margin-bottom: 6px;
}
.nombre-tematica {
  font-family: 'Rye', serif;
  font-size: 12px;
  letter-spacing: 1px;
}

.fila-habito {
  display: grid;
  grid-template-columns: 60px 1fr 80px 40px;
  gap: 10px;
  margin-bottom: 8px;
  align-items: center;
}
.fila-habito input {
  padding: 6px 10px;
  font-size: 14px;
  box-shadow: 1px 1px 0 var(--color-tinta);
}
.campo-icono {
  text-align: center;
  font-size: 22px;
}
.campo-puntos {
  text-align: center;
}
.boton-eliminar {
  background: var(--color-sangre);
  color: var(--color-papel);
  border: 1.5px solid var(--color-tinta);
  width: 32px;
  height: 32px;
  cursor: pointer;
  font-family: 'Rye', serif;
  font-size: 18px;
  box-shadow: 2px 2px 0 var(--color-tinta);
}
.boton-eliminar:hover {
  background: var(--color-tinta);
}

.error {
  color: var(--color-sangre);
  font-weight: 600;
  margin: 12px 0;
}
.botones-finales {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
}

@media (max-width: 600px) {
  .fila-habito {
    grid-template-columns: 50px 1fr 60px 40px;
  }
}
</style>
