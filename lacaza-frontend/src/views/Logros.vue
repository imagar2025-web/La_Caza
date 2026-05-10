<script setup>
// Vista de la galería de logros: medallas desbloqueadas y bloqueadas.

import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { listarLogros } from '../services/api'

const router = useRouter()
const logros = ref([])
const cargando = ref(true)

onMounted(async () => {
  await cargarLogros()
})

async function cargarLogros() {
  cargando.value = true
  try {
    const respuesta = await listarLogros()
    logros.value = respuesta.data
  } catch (e) {
    console.error(e)
  } finally {
    cargando.value = false
  }
}

const totalDesbloqueados = computed(() =>
  logros.value.filter(l => l.desbloqueado).length
)

// Calcula el porcentaje de progreso para mostrar la barra
function calcularPorcentaje(logro) {
  if (logro.desbloqueado) return 100
  return Math.min(100, (logro.progreso / logro.objetivo) * 100)
}
</script>

<template>
  <div class="contenedor">
    <div class="panel">
      <div class="cabecera-logros">
        <h2 class="titulo-vaquero">Galería de logros</h2>
        <button class="boton" @click="router.push('/')">← Volver</button>
      </div>

      <p class="descripcion">
        Cada logro se desbloquea con tus acciones reales. La recompensa: medalla + 25 XP.
      </p>

      <div v-if="cargando" class="cargando">Cargando logros...</div>

      <div v-else>
        <div class="contador">
          <span class="texto-monoespaciado">Desbloqueados:</span>
          <span class="valor-contador">{{ totalDesbloqueados }} / {{ logros.length }}</span>
        </div>

        <div class="rejilla-logros">
          <div
            v-for="logro in logros"
            :key="logro.idLogro"
            class="logro"
            :class="{ desbloqueado: logro.desbloqueado, bloqueado: !logro.desbloqueado }"
          >
            <div class="medalla">
              {{ logro.desbloqueado ? logro.icono : '?' }}
            </div>
            <div class="nombre-logro">{{ logro.nombre }}</div>
            <div class="descripcion-logro">{{ logro.descripcion }}</div>

            <div v-if="!logro.desbloqueado" class="barra-progreso">
              <div
                class="barra-relleno"
                :style="{ width: calcularPorcentaje(logro) + '%' }"
              ></div>
            </div>
            <div v-if="!logro.desbloqueado" class="texto-progreso">
              {{ logro.progreso }} / {{ logro.objetivo }}
            </div>
            <div v-else class="etiqueta-desbloqueado">DESBLOQUEADO</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.cabecera-logros {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
  padding-bottom: 10px;
  border-bottom: 2px solid var(--color-tinta);
}
.cabecera-logros h2 {
  font-size: 22px;
  color: var(--color-sangre);
}
.descripcion {
  font-style: italic;
  color: var(--color-tinta-suave);
  margin-bottom: 16px;
}
.contador {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
}
.valor-contador {
  font-family: 'Rye', serif;
  font-size: 18px;
  background: var(--color-tinta);
  color: var(--color-papel);
  padding: 4px 12px;
}
.cargando {
  text-align: center;
  padding: 40px;
  font-family: 'Special Elite', monospace;
}

.rejilla-logros {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 14px;
}
.logro {
  border: 2px solid var(--color-tinta);
  background: var(--color-papel);
  padding: 16px;
  text-align: center;
  box-shadow: 3px 3px 0 rgba(42, 24, 16, 0.15);
  transition: all 0.2s ease;
}
.logro.desbloqueado:hover {
  transform: translate(-2px, -2px);
  box-shadow: 5px 5px 0 var(--color-oro);
}
.logro.bloqueado {
  background: rgba(232, 217, 184, 0.4);
  filter: grayscale(0.7);
  opacity: 0.7;
}
.medalla {
  width: 60px;
  height: 60px;
  margin: 10px auto;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  border: 3px solid var(--color-tinta);
  background: radial-gradient(circle at 30% 30%, var(--color-oro-brillo), var(--color-oro) 70%);
  box-shadow: inset 0 0 0 2.5px var(--color-papel), 2px 2px 0 var(--color-tinta);
}
.logro.bloqueado .medalla {
  background: var(--color-papel-oscuro);
}
.nombre-logro {
  font-family: 'Rye', serif;
  font-size: 13px;
  letter-spacing: 0.5px;
  color: var(--color-tinta);
  margin-bottom: 4px;
}
.descripcion-logro {
  font-size: 13px;
  font-style: italic;
  color: var(--color-tinta-suave);
  line-height: 1.3;
  min-height: 34px;
}
.barra-progreso {
  margin-top: 10px;
  height: 6px;
  background: var(--color-papel-oscuro);
  border: 1px solid var(--color-tinta);
}
.barra-relleno {
  height: 100%;
  background: var(--color-sangre);
}
.texto-progreso {
  font-family: 'Special Elite', monospace;
  font-size: 9px;
  letter-spacing: 1.5px;
  color: var(--color-tinta-suave);
  margin-top: 4px;
}
.etiqueta-desbloqueado {
  font-family: 'Special Elite', monospace;
  font-size: 9px;
  letter-spacing: 1.5px;
  color: var(--color-sangre);
  margin-top: 8px;
}
</style>
