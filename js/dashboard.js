document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-item');
    const sections = document.querySelectorAll('.dashboard-section');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                
                navItems.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                
                const targetId = this.getAttribute('href').substring(1);
                sections.forEach(section => {
                    section.classList.remove('active');
                    if (section.id === targetId) {
                        section.classList.add('active');
                    }
                });
            }
        });
    });
});

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        closeModal(e.target.id);
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const activeModal = document.querySelector('.modal.active');
        if (activeModal) {
            closeModal(activeModal.id);
        }
    }
});

function editCategoria(id, nombre) {
    document.getElementById('edit-categoria-id').value = id;
    document.getElementById('edit-categoria-nombre').value = nombre;
    openModal('modal-edit-categoria');
}

function editProducto(producto) {
    document.getElementById('edit-producto-id').value = producto.id;
    document.getElementById('edit-producto-nombre').value = producto.nombre || '';
    document.getElementById('edit-producto-descripcion').value = producto.descripcion || '';
    document.getElementById('edit-producto-precio').value = producto.precio || 0;
    document.getElementById('edit-producto-categoria').value = producto.categoria_id || '';
    document.getElementById('edit-producto-imagen').value = producto.imagen || '';
    openModal('modal-edit-producto');
}

function editUsuario(usuario) {
    document.getElementById('edit-usuario-id').value = usuario.id;
    document.getElementById('edit-usuario-nombre').value = usuario.nombre_completo || '';
    document.getElementById('edit-usuario-correo').value = usuario.correo || '';
    document.getElementById('edit-usuario-username').value = usuario.nombre_usuario || '';
    document.getElementById('edit-usuario-password').value = '';
    openModal('modal-edit-usuario');
}

function exportData(format, section = 'all') {
    window.location.href = 'export.php?format=' + format + '&section=' + section;
}

const alerts = document.querySelectorAll('.alert');
alerts.forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }, 5000);
});

const tableRows = document.querySelectorAll('.data-table tbody tr');
tableRows.forEach(row => {
    row.addEventListener('mouseenter', function() {
        this.style.transition = 'all 0.3s ease';
    });
});

const statCards = document.querySelectorAll('.stat-card');
statCards.forEach((card, index) => {
    setTimeout(() => {
        card.style.animation = 'fadeInUp 0.6s ease forwards';
    }, index * 100);
});

const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

