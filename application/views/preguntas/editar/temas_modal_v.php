<!-- Modal -->
<div class="modal fade" id="temasModal" tabindex="-1" role="dialog" aria-labelledby="temasModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="temasModalLabel">Asignar tema</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

          <input
              type="text" class="form-control mb-1"
              title="Buscar por nombre, código o ID" placeholder="Buscar por nombre, código o ID"
              v-model="fields.q" v-on:change="getTemas"
          >
          <p class="text-center">{{ temas.length }} resultados</p>
          <table class="table bg-white">
            <tbody>
                <tr v-for="(tema, key) in temas">
                    <td>{{ tema.cod_tema }}</td>
                    <td>{{ tema.nombre_tema }}</td>
                    <td width="100px">
                        <button class="btn btn-light btn-sm" v-on:click="setTema(tema)">
                            Seleccionar
                        </button>
                    </td>
                </tr>
            </tbody>
          </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>