<script setup>
// Componente reusable que muestra una tarjeta de sala en el listado.
// Recibe la sala como propiedad (prop) y emite un evento al hacer clic.

// defineProps: declara qué propiedades acepta este componente
const props = defineProps({
  sala: {
    type: Object,
    required: true,
  },
  posicion: {
    type: Number,
    default: 1,
  },
})

// defineEmits: declara qué eventos puede emitir
const emit = defineEmits(['abrir'])

// Iconos según la temática
const iconosTematica = {
  deporte:   '🏋',
  estudio:   '📚',
  bienestar: '🧘',
  trabajo:   '💼',
  custom:    '✨',
}

// Etiquetas legibles
const etiquetasTematica = {
  deporte:   'DEPORTISTA',
  estudio:   'ESTUDIANTE',
  bienestar: 'BIENESTAR',
  trabajo:   'PRODUCTIVIDAD',
  custom:    'PERSONALIZADA',
}

function manejarClic() {
  emit('abrir', props.sala.idSala)
}
</script>

<template>
  <div class="tarjeta-sala" @click="manejarClic">
    <div class="icono-tematica">{{ iconosTematica[sala.tematica] || '✨' }}</div>
    <div class="etiqueta-tematica">{{ etiquetasTematica[sala.tematica] || 'CUSTOM' }}</div>
    <h3 class="nombre-sala">{{ sala.nombre }}</h3>

    <div class="meta-sala">
      <span>{{ sala.jugadores?.length || 0 }} CAZADORES</span>
      <span class="posicion">#{{ posicion }}</span>
    </div>
  </div>
</template>

<style scoped>
.tarjeta-sala {
  border: 2px solid var(--color-tinta);
  background: var(--color-papel);
  padding: 18px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 3px 3px 0 rgba(42, 24, 16, 0.18);
}
.tarjeta-sala:hover {
  transform: translate(-2px, -2px);
  box-shadow: 6px 6px 0 var(--color-oro);
}
.icono-tematica {
  font-size: 32px;
  margin-bottom: 10px;
}
.etiqueta-tematica {
  font-family: 'Special Elite', monospace;
  font-size: 9px;
  letter-spacing: 2px;
  color: var(--color-tinta-suave);
  text-transform: uppercase;
}
.nombre-sala {
  font-family: 'Rye', serif;
  font-size: 18px;
  letter-spacing: 1px;
  color: var(--color-tinta);
  margin: 4px 0 14px;
  line-height: 1.15;
}
.meta-sala {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-family: 'Special Elite', monospace;
  font-size: 10px;
  letter-spacing: 1.5px;
  color: var(--color-tinta-suave);
  text-transform: uppercase;
  padding-top: 10px;
  border-top: 1.5px dashed var(--color-tinta);
}
.posicion {
  color: var(--color-sangre);
  font-family: 'Rye', serif;
  font-size: 16px;
}
</style>
