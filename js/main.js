// Set current date
        // document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', { 
        //     weekday: 'long', 
        //     year: 'numeric', 
        //     month: 'long', 
        //     day: 'numeric' 
        // });
        
        // Toggle sidebar for mobile
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });
        
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', (event) => {
            if (!sidebar.contains(event.target) && !hamburger.contains(event.target) && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
        
        // Revenue Chart
       


        function toggleDropdown(clickedItem) {
            clickedItem.classList.toggle('active');
      }