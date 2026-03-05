<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="{{ asset('/adminAssets/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap -->
<script src="{{ asset('/adminAssets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('/adminAssets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('/adminAssets/dist/js/adminlte.js') }}"></script>
<script src="https://cdn.tiny.cloud/1/wd05dkkivqln7dmmsacvsw6dg8dbtl5smlbgb844h3d2e65q/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<!-- PAGE PLUGINS -->
<!-- jQuery Mapael -->
<script src="{{ asset('/adminAssets/plugins/jquery-mousewheel/jquery.mousewheel.js') }}"></script>
<script src="{{ asset('/adminAssets/plugins/raphael/raphael.min.js') }}"></script>
<script src="{{ asset('/adminAssets/plugins/jquery-mapael/jquery.mapael.min.js') }}"></script>
<script src="{{ asset('/adminAssets/plugins/jquery-mapael/maps/usa_states.min.js') }}"></script>
<!-- ChartJS -->
<script src="{{ asset('adminAssets/plugins/chart.js/Chart.min.js') }}"></script>
<!-- DataTables  & Plugins -->
<script src="{{ asset('adminAssets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" integrity="sha512-GWzVrcGlo0TxTRvz9ttioyYJ+Wwk9Ck0G81D+eO63BaqHaJ3YZX9wuqjwgfcV/MrB2PhaVX9DkYVhbFpStnqpQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{ asset('/custom.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="module">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
<!-- Summernote -->
<script src="{{ asset('adminAssets/plugins/summernote/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/select2/js/select2.full.min.js') }}"></script>
<!-- Bootstrap Switch -->
<script src="{{ asset('adminAssets/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}"></script>
<script src="{{ asset('adminAssets/tagsinput.min.js') }}"></script>
<script>
    $(function() {
        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        $('#example2').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });

        $("#customTable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": true,
            columnDefs: [{
                orderable: false,
                targets: [-1]
            }]
        });
    });
</script>

<script src="{{ asset('adminAssets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('adminAssets/plugins/jquery-validation/additional-methods.min.js') }}"></script>

<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<script type="text/javascript">
    setCookie('googtrans', '/en/fr', 30);
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({ 
            pageLanguage: 'en',
            // includedLanguages: 'fr',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE
        }, 'google_translate_element');
    }
    function setCookie(key, value, expiry) {
        var expires = new Date();
        expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
        document.cookie = key + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
    }
    document.addEventListener("DOMContentLoaded", function() {
        var chartInfoLabel = document.getElementById("chartInfoLabel");
        var transDescription = document.getElementById("trans_description_test");
        if (chartInfoLabel || transDescription) {
            chartInfoLabel.classList.add("notranslate");
            transDescription.classList.add("notranslate");
        }
    });
</script>
@yield('scripts')
