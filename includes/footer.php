<html>
    <head>
        <style>
            .social-icons {
    margin-top: 30px;
}

.container {
    position: relative; /* Makes the container the positioning context */
    min-height: 100px; /* Ensure the parent has a height */
}

.social-icons {
    position: absolute;
    margin-bottom: -10px;
    bottom: 0; /* Aligns to the bottom */
    left: 0; /* Aligns to the left */
    margin-top: 30px;
}

.social-icons a {
    display: inline-block;
    text-decoration: none;
    font-size: 30px;
    color: #ababab;
    transition: transform 0.5s;
}

.social-icons a:hover{
    color: aqua;
    transform: translateY(-5px);
}
        </style>
    </head>

</div> <!-- Close main-content -->
    </div> <!-- Close container -->
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Copyright. All rights reserved.</p>
            <div class="social-icons">
                        <a href="https://www.facebook.com/richardlegeek"><i class="fa-brands fa-facebook"></i></a>
                        <a href="https://x.com/Rizinkovic"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="https://github.com/Rizinkovic"><i class="fa-brands fa-github"></i></a>
                        <a href="https://www.linkedin.com/in/song-richard-9b4876353"><i class="fa-brands fa-linkedin"></i></a>
                    </div>
        </div>
    </footer>
    <script src="script.js"></script>
</body>
</html>