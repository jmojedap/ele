<?php
    $arr_flipbooks = array();

    foreach ($flipbooks->result() as $post)
    {
        //$att_img = $this->File_model->att_img($post->imagen_id, '500px_');
        $post->img_src = URL_CONTENT . 'books/covers/' . $post->slug . '.jpg';    ;
        $post->disponible = $this->pml->ago($post->published_at);
        $post->published_at_nice = $this->pml->date_format($post->published_at, 'M-d');
        $arr_flipbooks[] = $post;
    }
?>

<style>
    .cover_post{
        border: 1px solid #DDD;
        max-width: 120px;
        -webkit-border-radius: 2px;
        -moz-border-radius: 2px;
        border-radius: 2px;
        -webkit-box-shadow: 5px 5px 5px 0px rgba(227,227,227,1);
        -moz-box-shadow: 5px 5px 5px 0px rgba(227,227,227,1);
        box-shadow: 5px 5px 5px 0px rgba(227,227,227,1);
    }

    .cover_post:hover{
        border: 1px solid #AAA;
    }
</style>

<div id="product_flipbooks" class="center_box_750">
    <?php if ( $this->session->userdata('role') <= 2 ) { ?>
        <div class="card mb-2">
            <div class="card-body">
                <form accept-charset="utf-8" method="POST" id="flipbooks_form" @submit.prevent="add_flipbook" clas="form-horizontal">
                    <div class="form-group row">
                        <label for="post_id" class="col-md-2 col-form-label text-right">Contenido</label>
                        <div class="col-md-8">
                            <input
                                name="flipbook_id" id="field-flipbook_id" type="text" class="form-control"
                                required
                                title="ID Flipbook" placeholder="ID Flipbook"
                                v-model="flipbook_id"
                            >
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary btn-block" type="submit">
                                Agregar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php } ?>

    <table class="table bg-white">
        <thead>
            <th width="50px">ID</th>
            <th>Nombre</th>
            <th width="50px"></th>
        </thead>
        <tbody>
            <tr v-for="(flipbook, flipbook_key) in flipbooks">
                <td>{{ flipbook.id }}</td>
                <td>
                    <a v-bind:href="`<?php echo base_url("admin/flipbooks/info/") ?>` + `/` + flipbook.id">
                        {{ flipbook.title }}
                    </a>
                </td>
                <td>
                    <button class="btn btn-sm btn-warning" v-on:click="delete_meta(flipbook.meta_id)">
                        <i class="fa fa-times"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    new Vue({
        el: '#product_flipbooks',
        created: function(){
            //this.get_list();
        },
        data: {
            product_id: <?php echo $row->id ?>,
            flipbooks: <?php echo json_encode($arr_flipbooks) ?>,
            flipbook_id: ''
        },
        methods: {
            add_flipbook: function(){
                axios.get(app_url + 'products/add_flipbook/' + this.product_id + '/' + this.flipbook_id)
                .then(response => {
                    console.log(response.data)
                    window.location = app_url + 'products/flipbooks/' + this.product_id;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            delete_meta: function(meta_id){
                axios.get(app_url + 'products/delete_meta/' + this.product_id + '/' + meta_id)
                .then(response => {
                    console.log(response.data)
                    window.location = app_url + 'products/flipbooks/' + this.product_id;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
        }
    });
</script>