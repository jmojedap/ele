<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ordena los planetas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
</head>
<body>
  <div id="app" class="container mt-5">
    <h3>🪐 Ordena los planetas según su distancia al Sol (de menor a mayor)</h3>
    <ul id="sortable" class="list-group mb-3">
      <li
        v-for="planeta in planetas"
        :key="planeta.nombre"
        class="list-group-item"
        :data-nombre="planeta.nombre"
      >
        {{ planeta.nombre }}
      </li>
    </ul>
    <button class="btn btn-primary" @click="verificarOrden">Verificar orden</button>
  </div>

  <!-- Vue 3 -->
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
  <!-- jQuery y jQuery UI -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

  <script>
    const { createApp, onMounted, ref } = Vue;

    createApp({
      setup() {
        const planetasCorrecto = [
          { nombre: 'Mercurio', distancia: 57.9 },
          { nombre: 'Venus', distancia: 108.2 },
          { nombre: 'Tierra', distancia: 149.6 },
          { nombre: 'Marte', distancia: 227.9 },
          { nombre: 'Júpiter', distancia: 778.3 },
          { nombre: 'Saturno', distancia: 1427 },
          { nombre: 'Urano', distancia: 2871 },
          { nombre: 'Neptuno', distancia: 4497.1 }
        ];

        // Mezclar aleatoriamente los planetas
        const mezclar = (arr) => [...arr].sort(() => Math.random() - 0.5);
        const planetas = ref(mezclar(planetasCorrecto));

        onMounted(() => {
          $('#sortable').sortable();
        });

        const verificarOrden = () => {
          const ordenUsuario = $('#sortable')
            .children()
            .map(function () {
              return $(this).data('nombre');
            })
            .get();

          const ordenCorrecto = planetasCorrecto.map(p => p.nombre);
          const esCorrecto = JSON.stringify(ordenUsuario) === JSON.stringify(ordenCorrecto);

          if (esCorrecto) {
            alert('✅ ¡Muy bien! Has ordenado los planetas correctamente.');
          } else {
            alert('❌ El orden no es correcto. Intenta nuevamente.');
          }
        };

        return {
          planetas,
          verificarOrden
        };
      }
    }).mount('#app');
  </script>
</body>
</html>
