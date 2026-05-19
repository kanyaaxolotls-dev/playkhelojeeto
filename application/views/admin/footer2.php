</section>
</section>
<footer class="site-footer">
    <div class="text-center">
        <?= date('Y') ?> &copy; <?= $this->db_model->select('name', 'tbl_settings', array('id' => 1)) ?> Multigame Admin Panel.
        <a href="#" class="go-top">
            <i class="fa fa-angle-up"></i>
        </a>
    </div>
</footer>
<script src="<?= base_url('axxests/js/jquery.js') ?>"></script>
<script src="<?= base_url('axxests/js/bootstrap.bundle.min.js') ?>"></script>
<script class="include" type="text/javascript" src="<?= base_url('axxests/js/jquery.dcjqaccordion.2.7.js') ?>"></script>
<script src="<?= base_url('axxests/js/jquery.scrollTo.min.js') ?>"></script>
<script src="<?= base_url('axxests/js/jquery.nicescroll.js') ?>" type="text/javascript"></script>
<script src="<?= base_url('axxests/js/jquery.sparkline.js') ?>" type="text javascript"></script>
<script src="<?= base_url('axxests/assets/jquery-easy-pie-chart/jquery.easy-pie-chart.js') ?>"></script>
<script src="<?= base_url('axxests/js/owl.carousel.js') ?>"></script>
<script src="<?= base_url('axxests/js/jquery.customSelect.min.js') ?>"></script>
<script src="<?= base_url('axxests/js/respond.min.js') ?>"></script>
<script src="<?= base_url('axxests/js/slidebars.min.js') ?>"></script>
<script src="<?= base_url('axxests/js/common-scripts.js') ?>"></script>
<script src="<?= base_url('axxests/js/sparkline-chart.js') ?>"></script>
<script src="<?= base_url('axxests/js/easy-pie-chart.js') ?>"></script>
<script src="<?= base_url('axxests/js/count.js') ?>"></script>
<script src="<?= base_url('axxests/js/jquery.tagsinput.js') ?>"></script>
<script  src="<?= base_url('axxests/js/jquery-ui.min.js') ?>"></script>
<script  src="<?= base_url('axxests/js/bootstrap-switch.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('axxests/js/ga.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('axxests/assets/bootstrap-datepicker/js/bootstrap-datepicker.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('axxests/assets/bootstrap-daterangepicker/date.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('axxests/assets/bootstrap-daterangepicker/daterangepicker.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('axxests/assets/bootstrap-colorpicker/js/bootstrap-colorpicker.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('axxests/assets/ckeditor/ckeditor.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('axxests/assets/bootstrap-inputmask/bootstrap-inputmask.min.js') ?>"></script>
<script  src="<?= base_url('axxests/js/form-component.js') ?>"></script>
<script src="<?= base_url('axxests/assets/switchery/switchery.js') ?>"></script>
<script src="<?= base_url('axxests/assets/bootstrap-switch/static/js/bootstrap-switch.js') ?>"></script>
<script>
  $(document).ready(function() {
    $("#owl-demo").owlCarousel({
      navigation: true,
      slideSpeed: 300,
      paginationSpeed: 400,
      singleItem: true,
      autoPlay: true
    });
  });
  $(function() {
    $('select.styled').customSelect();
  });
  $(window).on("resize", function() {
    var owl = $("#owl-demo").data("owlCarousel");
    owl.reinit();
  });
</script>
<script>

jQuery(document).ready(function(){

    $('.summernote').summernote({
        height: 200,                 // set editor height

        minHeight: null,             // set minimum height of editor
        maxHeight: null,             // set maximum height of editor

        focus: true                 // set focus to editable area after initializing summernote
    });
});

</script>

<!--select2-->
<script type="text/javascript">

$(document).ready(function () {
    $(".js-example-basic-single").select2();

    $(".js-example-basic-multiple").select2();
});
</script>

<!--bootstrap swither-->
<script type="text/javascript">
$(document).ready(function () {
    // Resets to the regular style
    $('#dimension-switch').bootstrapSwitch('setSizeClass', '');
    // Sets a mini switch
    $('#dimension-switch').bootstrapSwitch('setSizeClass', 'switch-mini');
    // Sets a small switch
    $('#dimension-switch').bootstrapSwitch('setSizeClass', 'switch-small');
    // Sets a large switch
    $('#dimension-switch').bootstrapSwitch('setSizeClass', 'switch-large');


    $('#change-color-switch').bootstrapSwitch('setOnClass', 'success');
    $('#change-color-switch').bootstrapSwitch('setOffClass', 'danger');
});
</script>

<!-- swithery-->
<script type="text/javascript">
$(document).ready(function () {
    //default
    var elem = document.querySelector('.js-switch');
    var init = new Switchery(elem);


    //small
    var elem = document.querySelector('.js-switch-small');
    var switchery = new Switchery(elem, { size: 'small' });

    //large
    var elem = document.querySelector('.js-switch-large');
    var switchery = new Switchery(elem, { size: 'large' });


    //blue color
    var elem = document.querySelector('.js-switch-blue');
    var switchery = new Switchery(elem, { color: '#7c8bc7', jackColor: '#9decff' });

    //green color
    var elem = document.querySelector('.js-switch-yellow');
    var switchery = new Switchery(elem, { color: '#FFA400', jackColor: '#ffffff' });

    //red color
    var elem = document.querySelector('.js-switch-red');
    var switchery = new Switchery(elem, { color: '#ff6c60', jackColor: '#ffffff' });


});
</script>
</body>
</html>
