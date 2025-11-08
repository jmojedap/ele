<div id="panel_ia_app">
    <div class="container">
        <table class="table bg-white">
            <thead>
                <th>Mes</th>
                <th class="text-center">Tokens</th>
                <th class="text-center">Tarifa <br> M/Tokens (USD)</th>
                <th class="text-center">Tarifa <br> M/Tokens (COP)</th>
                <th class="text-center">Costo (COP)</th>
            </thead>
            <tbody>
                <tr class="bg-info text-white">
                    <td><strong>Total</strong></td>
                    <td class="text-center"><strong>{{ token_count_summary['sum'] }}</strong></td>
                </tr>
                <tr v-for="(month, key) in months">
                    <td>{{ month.mes_formateado }}</td>
                    <!-- Tokens separados por punto de miles -->
                    <td class="text-center">{{ month.sum_total_token_count.toLocaleString('es-CO', { style: 'decimal' } ) }}</td>
                    <td class="text-center">
                        {{
                            getTarifaDolar(month.mes_formateado).toLocaleString('es-CO', { style: 'currency', currency: 'USD' })
                        }}
                    </td>
                    <td class="text-center">
                        {{
                            getTarifaPesos(month.mes_formateado).toLocaleString('es-CO', { style: 'currency', currency: 'COP' })
                        }}
                    </td>
                    <td class="text-center">
                        {{
                            (
                                (month.sum_total_token_count / 1000000) *
                                getTarifaPesos(month.mes_formateado)
                            ).toLocaleString('es-CO', { style: 'currency', currency: 'COP' })
                        }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
var panel_ia_app = new Vue({
    el: '#panel_ia_app',
    created: function(){
        //this.get_list()
    },
    data: {
        months: <?= json_encode($month_summarize->result()) ?>,
        token_count_summary: <?= json_encode($token_count_summary) ?>,
        loading: false,
        months_tarifas: [
            { mes: '2025-10', tarifa: 0.4, dolar: 4100 },
            { mes: '2025-09', tarifa: 0.4, dolar: 4100 },
            { mes: '2025-08', tarifa: 0.4, dolar: 4100 },
        ]
    },
    methods: {
        getTarifaDolar(mes) {
            let tarifa_dolar = this.months_tarifas.find( t => t.mes === mes );
            return tarifa_dolar ? tarifa_dolar.tarifa : 4100;
        },
        getTarifaPesos(mes) {
            let tarifa_mes = this.months_tarifas.find( t => t.mes === mes );
            return tarifa_mes ? tarifa_mes.dolar * tarifa_mes.tarifa : 4100;
        },
    }
})
</script>