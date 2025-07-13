<script setup lang="ts">
import safeBoxWithGoldenCoin from '@images/misc/3d-safe-box-with-golden-dollar-coins.png'

interface Pricing {
  title?: string
  xs?: number | string
  sm?: number | string
  md?: string | number
  lg?: string | number
  xl?: string | number
}

const props = defineProps<Pricing>()

const formatRupiah = (amount: number): string => {
  const formatted = amount
    .toFixed(2) 
    .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    .replace('.', ',') 

  return `Rp. ${formatted}`
}

const pricingPlans = [
  {
    name: 'Anggota Aktif',
    tagLine: 'Biaya pendaftaran lengkap untuk siswa baru',
    logo: safeBoxWithGoldenCoin,
    isPopular: true,
    biayaMasuk: 250000,
    biayaBulanan: [
      {
        ageRange: '9–12 tahun',
        price: 50000,
      },
      {
        ageRange: '13–15 tahun',
        price: 75000,
      },
    ],
    features: [
      '1 Set Jersey Latihan (atas & bawah)',
      'ID Card Anggota',
      'Latihan rutin bersama pelatih bersertifikat',
      'Akses ke turnamen dan pertandingan resmi',
    ],
  },
]
</script>

<template>
  <div class="pricing-wrapper">
    <div class="text-center mb-8">
      <h3 class="text-h3 pricing-title mb-4">Biaya Pendaftaran SSB</h3>
      <p class="text-body-1 mb-2">
        Menjadi bagian dari SSB berarti mendapatkan pelatihan berkualitas, perlengkapan lengkap, dan pengalaman bertanding yang seru.
      </p>
      <p class="text-body-1 mb-0">
        Dapatkan semua fasilitas dalam satu paket pendaftaran yang dirancang khusus untuk mendukung perkembangan anak Anda di dunia sepak bola.
      </p>
    </div>

    <div class="pricing-container">
      <div 
        v-for="plan in pricingPlans" 
        :key="plan.name"
        class="pricing-card-wrapper"
      >
        <div class="pricing-card" :class="{ 'popular-card': plan.isPopular }">
          <div class="card-content">
            <div class="logo-section">
              <img
                src="/storage/logo/LOGOSSB.png"
                alt="Logo SSB"
                class="app-logo-img"
                style="height: 70px;"
              />
            </div>

            <div class="plan-info">
              <h4 class="plan-name">{{ plan.name }}</h4>
              <p class="plan-tagline">{{ plan.tagLine }}</p>
            </div>

            <!-- Harga -->
            <div class="price-section">
              <div class="price-display">
                <span class="price-amount">{{ formatRupiah(plan.biayaMasuk) }}</span>
                <span class="price-period">Biaya Masuk (sekali bayar)</span>
              </div>

              <hr class="divider mt-3 mb-3" />

              <div class="monthly-price-label">Biaya Bulanan:</div>
              <div
                v-for="(item, index) in plan.biayaBulanan"
                :key="index"
                class="monthly-price-item"
              >
                <span class="monthly-price-amount">{{ formatRupiah(item.price) }}</span>
                <span class="monthly-price-text">/bulan untuk anak usia {{ item.ageRange }}</span>
              </div>
            </div>

            <!-- Fitur -->
            <div class="features-section">
              <ul class="features-list">
                <li 
                  v-for="feature in plan.features" 
                  :key="feature"
                  class="feature-item"
                >
                  <span class="feature-icon">✓</span>
                  <span class="feature-text">{{ feature }}</span>
                </li>
              </ul>
            </div>

            <!-- CTA -->
            <div class="cta-section">
              <button 
                class="cta-button"
                @click="$router.push({ name: 'pages-authentication-register-multi-steps' })"
              >
                Daftar Sekarang
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.pricing-wrapper {
  width: 100%;
  max-width: 100%;
  margin: 0;
  padding: 0;
}

.pricing-container {
  width: 100%;
  display: flex;
  justify-content: center;
  margin: 0;
  padding: 0;
}

.pricing-card-wrapper {
  width: 100%;
  max-width: 600px;
  margin: 0;
  padding: 0;
}

.pricing-card {
  width: 100%;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  
  &:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
  }
  
  &.popular-card {
    border-color: rgb(var(--v-theme-primary));
    border-width: 2px;
  }
}

.card-header {
  display: flex;
  justify-content: flex-end;
  padding: 16px 24px 0;
  min-height: 40px;
}

.popular-badge {
  background: rgb(var(--v-theme-primary));
  color: white;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.card-content {
  padding: 0 24px 24px;
  margin-top: 20px;
}

.logo-section {
  display: flex;
  justify-content: center;
  margin-bottom: 24px;
}

.plan-logo {
  width: 120px;
  height: 120px;
  object-fit: contain;
}

.plan-info {
  text-align: center;
  margin-bottom: 32px;
}

.plan-name {
  font-size: 28px;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 8px;
}

.plan-tagline {
  font-size: 16px;
  color: #666;
  margin: 0;
}

.price-section {
  text-align: center;
  margin-bottom: 32px;
}

.price-display {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.price-amount {
  font-size: 36px;
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
  line-height: 1;
}

.price-period {
  font-size: 14px;
  color: #666;
  font-weight: 500;
}

.monthly-price-label {
  font-size: 14px;
  color: #666;
  font-weight: 500;
}

.monthly-price-item {
  font-size: 14px;
  color: #666;
  font-weight: 500;
}

.features-section {
  margin-bottom: 32px;
}

.features-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.feature-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 12px;
  
  &:last-child {
    margin-bottom: 0;
  }
}

.feature-icon {
  width: 20px;
  height: 20px;
  background: rgb(var(--v-theme-primary));
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
  flex-shrink: 0;
  margin-top: 2px;
}

.feature-text {
  font-size: 16px;
  color: #333;
  line-height: 1.5;
}

.cta-section {
  width: 100%;
}

.cta-button {
  width: 100%;
  background: rgb(var(--v-theme-primary));
  color: white;
  border: none;
  padding: 16px 24px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  
  &:hover {
    background: rgba(var(--v-theme-primary), 0.9);
    transform: translateY(-1px);
  }
  
  &:active {
    transform: translateY(0);
  }
}

// Responsive design
@media (max-width: 768px) {
  .pricing-card-wrapper {
    max-width: 100%;
  }
  
  .card-content {
    padding: 0 16px 16px;
  }
  
  .plan-name {
    font-size: 20px;
  }
  
  .price-amount {
    font-size: 28px;
  }
  
  .feature-text {
    font-size: 14px;
  }
}
</style>
