<div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="row page-titles only-sm">
                    <div class="col-md-6 col-8 align-self-center">
                        <h3 class="text-themecolor mb-0 mt-0">
                            <span id="head_title">
                                <?php echo $head_title ?>
                            </span>
                            <?php if ( ! is_null($head_subtitle) ) { ?>
                                <span id="head_subtitle" style="font-size: 0.8em; color: #999; padding-left: 0px;" class="">
                                    <i class="fa fa-chevron-right"></i>
                                    <?php echo $head_subtitle ?>
                                </span>
                            <?php } ?>
                        </h3>
                    </div>
                </div>

                <?php if ( ! is_null($view_description) ) { ?>
                    <div id="view_description">
                        <?php $this->load->view($view_description) ?>
                    </div>
                <?php } ?>

                <?php if ( ! is_null($nav_2) ) { ?>
                    <div id="nav_2">
                        <?php $this->load->view($nav_2) ?>
                    </div>
                <?php } ?>

                <?php if ( ! is_null($nav_3) ) { ?>
                    <div id="nav_3">
                        <?php $this->load->view($nav_3) ?>
                    </div>
                <?php } ?>

                <?php $this->load->view($view_a) ?>

                <?php if ( ! is_null($view_b) ) { ?>
                    <div id="view_b">
                        <?php $this->load->view($view_b) ?>
                    </div>
                <?php } ?>
                
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->

            
            <footer class="footer">
                © 2025 En Línea Editores &middot; Colombia
            </footer>
            
        </div>
        