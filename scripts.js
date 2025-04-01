// Update path API untuk setiap halaman
const API_BASE = location.pathname.includes('/pages/') ? '../api/' : 'api/';

async function getProductInfo() {
    const productName = document.getElementById('product-name').value;
    const productDetailsDiv = document.getElementById('product-details');

    // Tampilkan loading
    productDetailsDiv.innerHTML = '<div class="loading">Mencari informasi produk...</div>';
    productDetailsDiv.style.opacity = '0.7';

    try {
        const response = await fetch(`api.php?product_name=${encodeURIComponent(productName)}`);
        if (response.ok) {
            const productDetails = await response.text();
            productDetailsDiv.style.opacity = '0';
            setTimeout(() => {
                productDetailsDiv.innerHTML = productDetails;
                productDetailsDiv.style.opacity = '1';
            }, 300);
        } else {
            productDetailsDiv.innerHTML = "Gagal mengambil informasi produk.";
        }
    } catch (error) {
        console.error('Error:', error);
        productDetailsDiv.innerHTML = "Terjadi kesalahan saat mengambil informasi produk.";
    }
}


async function addSchedule() {
    const scheduleName = document.getElementById('schedule-name').value;
    const scheduleTime = document.getElementById('schedule-time').value;
    const scheduleList = document.getElementById('schedule-list');

    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `schedule_name=${encodeURIComponent(scheduleName)}&schedule_time=${encodeURIComponent(scheduleTime)}`
        });

        if (response.ok) {
            const message = await response.text();
            console.log(message);

            // Membuat elemen jadwal dengan animasi
            const listItem = document.createElement('div');
            listItem.className = 'schedule-item';
            listItem.innerHTML = `
                <strong>${scheduleName}</strong>
                <p>${new Date(scheduleTime).toLocaleString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })}</p>
            `;
            scheduleList.appendChild(listItem);

            // Reset form
            document.getElementById('schedule-name').value = '';
            document.getElementById('schedule-time').value = '';
        } else {
            console.error('Gagal menambahkan jadwal.');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Tambahkan JavaScript untuk skin analysis
document.addEventListener('DOMContentLoaded', function() {
    const skinQuizForm = document.getElementById('skinQuizForm');
    if (!skinQuizForm) return;

    // Tambahkan event handler untuk skin type selection
    const skinTypeCards = document.querySelectorAll('.skin-type-card');
    skinTypeCards.forEach(card => {
        card.addEventListener('click', function() {
            // Hapus selected class dari semua card
            skinTypeCards.forEach(c => c.classList.remove('selected'));
            // Tambahkan selected class ke card yang diklik
            this.classList.add('selected');
        });
    });

    const steps = skinQuizForm.querySelectorAll('.quiz-step');
    const progressSteps = document.querySelectorAll('.step');
    const progress = document.querySelector('.progress');
    const stepInfo = document.querySelector('.step-info');
    const prevBtn = skinQuizForm.querySelector('.btn-prev');
    const nextBtn = skinQuizForm.querySelector('.btn-next');
    const submitBtn = skinQuizForm.querySelector('.btn-submit');
    let currentStep = 1;

    function updateProgress(step) {
        const percent = ((step - 1) / (steps.length - 1)) * 100;
        progress.style.width = `${percent}%`;

        progressSteps.forEach((stepEl, idx) => {
            if (idx + 1 < step) {
                stepEl.classList.add('completed');
                stepEl.classList.add('active');
            } else if (idx + 1 === step) {
                stepEl.classList.add('active');
                stepEl.classList.remove('completed');
            } else {
                stepEl.classList.remove('active', 'completed');
            }
        });
    }

    function updateStep(step) {
        steps.forEach(s => s.classList.remove('active'));
        steps[step-1].classList.add('active');
        
        updateProgress(step);
        stepInfo.textContent = `Langkah ${step} dari ${steps.length}`;
        
        prevBtn.style.display = step === 1 ? 'none' : 'block';
        if (step === steps.length) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'block';
        } else {
            nextBtn.style.display = 'block';
            submitBtn.style.display = 'none';
        }
    }

    // Navigation handlers
    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateStep(currentStep);
        }
    });

    nextBtn.addEventListener('click', () => {
        let canProceed = true;

        // Validasi setiap langkah
        if (currentStep === 1) {
            const selectedSkinType = skinQuizForm.querySelector('.skin-type-card.selected');
            if (!selectedSkinType) {
                alert('Silakan pilih jenis kulit Anda');
                canProceed = false;
            }
        } else if (currentStep === 2) {
            const selectedConcerns = skinQuizForm.querySelectorAll('input[name="concerns[]"]:checked');
            if (selectedConcerns.length === 0) {
                alert('Silakan pilih minimal satu masalah kulit');
                canProceed = false;
            }
        }

        if (canProceed && currentStep < steps.length) {
            currentStep++;
            updateStep(currentStep);
        }
    });

    // Form submission
    skinQuizForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validasi final
        const selectedSkinType = skinQuizForm.querySelector('.skin-type-card.selected');
        const selectedConcerns = skinQuizForm.querySelectorAll('input[name="concerns[]"]:checked');
        const routineFrequency = skinQuizForm.querySelector('select[name="routine_frequency"]').value;

        if (!selectedSkinType || selectedConcerns.length === 0 || !routineFrequency) {
            alert('Mohon lengkapi semua informasi');
            return;
        }

        const formData = new FormData(skinQuizForm);
        formData.append('skinType', selectedSkinType.dataset.type);

        try {
            const response = await fetch('api/analyze-skin.php', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const result = await response.json();
                displayAnalysisResult(result);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });

    // Initialize first step
    updateStep(1);
});

function displayAnalysisResult(result) {
    const resultDiv = document.getElementById('analysisResult');
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = `
        <h3>Hasil Analisis Kulit Anda</h3>
        <div class="result-content">
            <div class="skin-profile">
                <h4>Profil Kulit</h4>
                <p><strong>Jenis Kulit:</strong> ${result.skinType}</p>
                <p><strong>Masalah Utama:</strong> ${result.concerns.join(', ')}</p>
            </div>
            <div class="recommendations">
                <h4>Rekomendasi Produk</h4>
                <div class="product-recommendations">
                    ${result.recommendations.map(product => `
                        <div class="recommended-product">
                            <img src="${product.image_url || 'images/default-product.jpg'}" 
                                 alt="${product.name}">
                            <h5>${product.name}</h5>
                            <p>${product.description}</p>
                        </div>
                    `).join('')}
                </div>
            </div>
            <div class="routine-tips">
                <h4>Tips Perawatan</h4>
                <ul>
                    ${result.tips.map(tip => `<li>${tip}</li>`).join('')}
                </ul>
            </div>
        </div>
    `;
}

// Load Trending Products
async function loadTrendingProducts() {
    try {
        const response = await fetch(`${API_BASE}trending-products.php`);
        if (response.ok) {
            const products = await response.json();
            displayTrendingProducts(products);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Load Articles
async function loadArticles() {
    try {
        const response = await fetch(`${API_BASE}articles.php`);
        if (response.ok) {
            const articles = await response.json();
            displayArticles(articles);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Panggil fungsi saat halaman dimuat
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Load trending products
        const trendingResponse = await fetch('api.php?trending=1');
        if (trendingResponse.ok) {
            const products = await trendingResponse.json();
            displayTrendingProducts(products);
        }

        // Load articles
        const articlesResponse = await fetch('api.php?articles=1');
        if (articlesResponse.ok) {
            const articles = await articlesResponse.json();
            displayArticles(articles);
        }
    } catch (error) {
        console.error('Error loading data:', error);
    }
});

// Fungsi untuk menampilkan produk trending
function displayTrendingProducts(products) {
    const carousel = document.querySelector('.product-carousel');
    if (!carousel) return;

    const defaultProducts = [
        {
            name: "Gentle Foam Cleanser",
            brand: "Pure Beauty",
            price: "125000",
            image_url: "https://images.unsplash.com/photo-1631730359585-38a4935cbec4",
            avg_rating: 4.5,
            review_count: 120
        },
        {
            name: "Hydrating Toner",
            brand: "Skin Essentials",
            price: "180000",
            image_url: "https://images.unsplash.com/photo-1624937673538-91789c5a4d0a",
            avg_rating: 4.8,
            review_count: 95
        },
        {
            name: "Vitamin C Serum",
            brand: "Glow Lab",
            price: "350000",
            image_url: "https://images.unsplash.com/photo-1620916297397-a4a5402a3c6c",
            avg_rating: 4.7,
            review_count: 150
        }
    ];

    const productsToDisplay = products.length > 0 ? products : defaultProducts;

    carousel.innerHTML = productsToDisplay.map(product => `
        <div class="product-card">
            <img src="${product.image_url}" 
                 alt="${product.name}"
                 onerror="this.src='https://images.unsplash.com/photo-1556228720-195a672e8a03'">
            <h3>${product.name}</h3>
            <p class="brand">${product.brand}</p>
            <p class="price">Rp ${parseFloat(product.price).toLocaleString('id-ID')}</p>
            <div class="rating">
                ${'★'.repeat(Math.round(product.avg_rating))}${'☆'.repeat(5-Math.round(product.avg_rating))}
                (${product.review_count} ulasan)
            </div>
        </div>
    `).join('');
}

// Fungsi untuk menampilkan artikel
function displayArticles(articles) {
    const grid = document.querySelector('.articles-grid');
    if (!grid) return;

    const defaultArticles = [
        {
            title: "Cara Merawat Kulit Berminyak",
            content: "Tips dan trik untuk mengatasi kulit berminyak dan mencegah jerawat...",
            image_url: "https://images.unsplash.com/photo-1616394584738-fc6e612e71b9",
            author: "Dr. Sarah",
            created_at: new Date()
        },
        {
            title: "Urutan Skincare yang Benar",
            content: "Panduan lengkap urutan pemakaian skincare untuk hasil maksimal...",
            image_url: "https://images.unsplash.com/photo-1598440947619-2c35fc9aa908",
            author: "Beauty Expert",
            created_at: new Date()
        },
        {
            title: "Pentingnya Sunscreen",
            content: "Mengapa sunscreen penting untuk kesehatan dan kecantikan kulit...",
            image_url: "https://images.unsplash.com/photo-1605897472359-85e4b94d685d",
            author: "Skin Specialist",
            created_at: new Date()
        }
    ];

    const articlesToDisplay = articles.length > 0 ? articles : defaultArticles;

    grid.innerHTML = articlesToDisplay.map(article => `
        <div class="article-card">
            <img src="${article.image_url}" 
                 alt="${article.title}"
                 onerror="this.src='https://images.unsplash.com/photo-1556228852-6d35a585d566'"
                 class="article-image">
            <div class="article-content">
                <h3>${article.title}</h3>
                <p>${article.content.substring(0, 150)}...</p>
                <small>Oleh ${article.author} - ${new Date(article.created_at).toLocaleDateString('id-ID')}</small>
            </div>
        </div>
    `).join('');
}
