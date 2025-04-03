document.addEventListener('DOMContentLoaded', function() {
    const aramaFormu = document.getElementById('arama-formu');
    const aramaKutusu = document.getElementById('arama-kutusu');
    aramaFormu.addEventListener('submit', function(e) {
        if (aramaKutusu.value.trim() === '') {
            e.preventDefault();
            alert('Lütfen bir arama terimi girin.');
            return;
        }
    });
    aramaKutusu.addEventListener('input', function() {
        const aramaTerimi = this.value.trim();
        if (aramaTerimi.length>=3) {
            fetch('arama_onerileri.php?q=' + encodeURIComponent(aramaTerimi))
                .then(response => response.json())
                .then(data => {
                    console.log('Arama önerileri:', data);
                }).catch(error => console.error('Hata:', error));
        }
    });
}); 