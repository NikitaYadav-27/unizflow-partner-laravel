
</div>
        </div>
        <!-- End::app-content -->


        <!-- Footer Start -->
        <footer class="footer mt-auto py-3 bg-white text-center">
            <div class="container">
                <span class="text-muted"> Copyright © <span id="year"><?php echo date("Y");?></span> <a href="javascript:void(0);" class="text-dark fw-semibold">{!! env("app.title"); !!}</a>.
                    </a> All
                    rights
                    reserved
                </span>
            </div>
        </footer>
        <!-- Footer End -->

    </div>


    
<style>

.custom-card .card-header {
    background: linear-gradient(124deg, rgba(120, 103, 58, 0.7) 0%, rgb(var(--primary-rgb)) 100%);
    color: white;
}
    th,td{
        text-align: left !important;
    }

    .page{
       justify-content: start !important; 
    }

    

 */
 


</style>
    
    <!-- Scroll To Top -->
    <div class="scrollToTop">
        <span class="arrow"><i class="ri-arrow-up-s-fill fs-20"></i></span>
    </div>
    <div id="responsive-overlay"></div>
    <!-- Scroll To Top -->



    <?php echo view('includes/js', get_defined_vars()); ?>
    
<script src="/assets/js/custom-switcher.min.js?v=767"></script>
<script src="/assets/js/defaultmenu.min.js?v=788"></script>   
    
   

    
    
</body>

</html>