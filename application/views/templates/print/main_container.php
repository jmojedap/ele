<script>
    $('.dropdown-toggle').dropdown();
</script>

<div class="main_container container-fluid">   
    <div class="row" style="margin-bottom: 5px;">
        <div class="col-md-12">
            <div class="" style="display: inline-block">
                <h1>
                    <?php echo $head_title ?>
                    <?php if ( ! is_null($head_subtitle) ) : ?>
                        <span style="font-size: 0.7em; color: #333; padding-left: 0px;" class="hidden-xs"><?php echo $head_subtitle ?></span>
                    <?php endif ?>
                </h1>
            </div>
        </div>
    </div>
    <?php $this->load->view($view_a) ?>
</div>
<footer class="main_footer">En Línea Editores &copy; 2025</footer>