
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar">
            
            

            <nav class="navbar top-navbar navbar-expand-md navbar-light">

                <!-- ============================================================== -->
                <!-- Logo -->
                <!-- ============================================================== -->
                <div class="navbar-header">
                    <a class="navbar-brand" href="<?php echo base_url() ?>">
                        <!-- Logo icon -->
                        <b>
                            <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                            <!-- Dark Logo icon -->
                            <img src="<?php echo URL_IMG ?>monster/logo-light-icon.png" alt="homepage" class="dark-logo" />
                            <!-- Light Logo icon -->
                            <img src="<?php echo URL_IMG ?>monster/logo-light-icon.png" alt="homepage" class="light-logo" />
                        </b>
                        <!--End Logo icon -->
                        <!-- Logo text -->
                        <span>
                        <!-- dark Logo text -->
                        <img src="<?php echo URL_IMG ?>monster/logo-light-text.png" alt="homepage" class="dark-logo" />
                        <!-- Light Logo text -->    
                        <img src="<?php echo URL_IMG ?>monster/logo-light-text.png" class="light-logo" alt="homepage" /></span>
                    </a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->


                <div class="d-none d-lg-block" id="nav_title">
                    <h1 id="head_title" class="head_title">
                        <?php echo $head_title ?>
                    </h1>
                    <h2 id="head_subtitle" class="head_subtitle">
                        &middot;
                        <?php echo $head_subtitle ?>
                    </h2>
                </div>
                
                <div class="navbar-collapse">

                    

                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav mr-auto mt-md-0 ">
                        <!-- This is  -->
                        <li class="nav-item">
                            <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)">
                                <i class="ti-menu"></i>
                            </a>
                        </li>
                        <li class="nav-item"> <a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="icon-arrow-left-circle"></i></a> </li>
                        
                        
                    </ul>
                    
                </div>
            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->