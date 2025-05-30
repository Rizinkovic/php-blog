// Basic JavaScript for future enhancements
document.addEventListener('DOMContentLoaded', function() {
    console.log('Blog system loaded');
    
    // Example: Add click event to all "Read more" links
    const readMoreLinks = document.querySelectorAll('.post-content a');
    readMoreLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            console.log('Navigating to article');
        });
    });
    
    // Future feature: Dark mode toggle
    const darkModeToggle = document.createElement('button');
    darkModeToggle.textContent = 'Toggle Dark Mode';
    darkModeToggle.classList.add('dark-mode-toggle');
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
    });
    
    // Add to header if needed
    //*document.querySelector('.site-header .container').appendChild(darkModeToggle);
});