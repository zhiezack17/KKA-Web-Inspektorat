   <script>
  // Auto-close flash after 5s
  document.querySelectorAll('.flash').forEach(el=>{
    setTimeout(()=>{ el.style.opacity='0'; setTimeout(()=>el.remove(), 400); }, 5000);
  });

  // Format currency input
  document.querySelectorAll('input[data-money]').forEach(inp=>{
    const fmt = v => {
      v = String(v||'').replace(/[^\d]/g,'');
      if(!v) return '';
      return v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };
    inp.value = fmt(inp.value);
    inp.addEventListener('input', e => { e.target.value = fmt(e.target.value); });
  });

  // Mobile sidebar toggle
  (function(){
    const btn = document.getElementById('mobileMenuBtn');
    const closeBtn = document.getElementById('sidebarCloseBtn');
    const sb  = document.querySelector('.sidebar');
    const bd  = document.getElementById('mobileBackdrop');
    if(!sb || !bd) return;

    function closeMenu(){
      sb.classList.remove('open');
      bd.classList.remove('show');
      document.body.classList.remove('menu-open');
    }
    function openMenu(){
      sb.classList.add('open');
      bd.classList.add('show');
      document.body.classList.add('menu-open');
    }

    if (btn) btn.addEventListener('click', function(e){ e.preventDefault(); openMenu(); });
    if (closeBtn) closeBtn.addEventListener('click', function(e){ e.preventDefault(); closeMenu(); });

    bd.addEventListener('click', closeMenu);
    document.querySelectorAll('.sidebar .nav-item, .sidebar .logout').forEach(el=>{
      el.addEventListener('click', closeMenu);
    });

    window.addEventListener('resize', function(){
      if(window.innerWidth > 992) closeMenu();
    });
  })();

  // Desktop AI-Style Collapsible Sidebar
  (function(){
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    
    function toggleSidebar(){
      if (window.innerWidth <= 992) return;
      const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
      document.documentElement.classList.toggle('sidebar-collapsed', isCollapsed);
      try {
        localStorage.setItem('kka_sidebar_collapsed', isCollapsed ? '1' : '0');
      } catch (e) {}
    }

    if (toggleBtn) {
      toggleBtn.addEventListener('click', function(e){
        e.preventDefault();
        toggleSidebar();
      });
    }

    if (collapseBtn) {
      collapseBtn.addEventListener('click', function(e){
        e.preventDefault();
        toggleSidebar();
      });
    }

    // Shortcut Ctrl+B / Cmd+B (AI Editor Standard)
    document.addEventListener('keydown', function(e){
      if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
        const tag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        if (tag === 'input' || tag === 'textarea' || tag === 'select') return;
        e.preventDefault();
        toggleSidebar();
      }
    });
  })();
</script>
</body>
</html>