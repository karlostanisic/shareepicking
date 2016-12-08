        </div>
    </div>    
    <script src="js/bootstrap.min.js"></script>
    <script>
<?php
if ($activeNavLink !== "") {
    echo "$('#$activeNavLink').addClass('active')";
}
?>        
        $(document).ready(function() {
            var stickyNavTop = $('#sticky-nav').offset().top;
            
            var stickyNav = function(){
                var scrollTop = $(window).scrollTop();

                if (scrollTop > stickyNavTop) {
                    $('#sticky-nav-placeholder').height($('#sticky-nav').height());
                    $('#sticky-nav').addClass('sticky');
                    
                } else {
                    $('#sticky-nav').removeClass('sticky');
                    $('#sticky-nav-placeholder').height(0);
                }
            };

            stickyNav();

            $(window).scroll(function() {
              stickyNav();
            });
            
        }); 
    </script>
</body>
</html>