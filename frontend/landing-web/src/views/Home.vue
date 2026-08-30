<template>
  <div class="home">
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-bg">
        <div class="hero-grid"></div>
        <div class="hero-glow"></div>
      </div>
      <div class="container">
        <div class="hero-content">
          <div class="hero-badge">
            <span class="badge-dot"></span>
            辰科 · 多租户 MES 制造执行系统
          </div>
          <h1>
            让车间生产<br>
            <span class="gradient-text">全程可视化、可追溯</span>
          </h1>
          <p class="hero-desc">
            覆盖订单接收、BOM、排产、质检、库存、采购、工资核算全流程。<br>
            多租户数据隔离 + 域名绑定，支持中小型加工厂私有化部署。
          </p>
          <div class="hero-actions">
            <router-link to="/features" class="btn btn-primary btn-large">
              查看功能
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </router-link>
            <router-link to="/deploy" class="btn btn-secondary btn-large">
              私有部署
            </router-link>
          </div>
        </div>
        <div class="hero-visual">
          <div class="hero-card">
            <div class="card-header">
              <div class="card-dots">
                <span></span><span></span><span></span>
              </div>
              <span class="card-title">生产数据大屏</span>
            </div>
            <div class="card-body">
              <div class="kpi-grid">
                <div class="kpi">
                  <span class="kpi-label">今日产量</span>
                  <span class="kpi-value">1,286 <em>件</em></span>
                </div>
                <div class="kpi">
                  <span class="kpi-label">良品率</span>
                  <span class="kpi-value">98.6 <em>%</em></span>
                </div>
                <div class="kpi">
                  <span class="kpi-label">在制订单</span>
                  <span class="kpi-value">37 <em>单</em></span>
                </div>
                <div class="kpi">
                  <span class="kpi-label">设备稼动</span>
                  <span class="kpi-value">91.2 <em>%</em></span>
                </div>
              </div>
              <div class="flow-row">
                <div class="flow-step done">接单</div>
                <div class="flow-arrow"></div>
                <div class="flow-step done">排产</div>
                <div class="flow-arrow"></div>
                <div class="flow-step active">生产</div>
                <div class="flow-arrow"></div>
                <div class="flow-step">质检</div>
                <div class="flow-arrow"></div>
                <div class="flow-step">出库</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
      <div class="container">
        <div class="stats-grid">
          <div class="stat-item" v-for="stat in stats" :key="stat.label">
            <div class="stat-value">
              <CounterUp :value="stat.value" :suffix="stat.suffix" />
            </div>
            <div class="stat-label">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="section">
      <div class="container">
        <SectionTitle
          tag="核心功能"
          title="覆盖制造全流程的一体化 MES"
          description="从订单接收到成品出库，从计划到追溯，全方位支撑车间数字化管理"
          :center="true"
        />
        <div class="features-grid">
          <ScrollReveal v-for="(feature, index) in features" :key="feature.title" :delay="index * 100">
            <FeatureCard
              :icon="feature.icon"
              :title="feature.title"
              :description="feature.description"
              :features="feature.features"
              :color="feature.color"
            />
          </ScrollReveal>
        </div>
      </div>
    </section>

    <!-- Module Section -->
    <section class="section section-alt">
      <div class="container">
        <SectionTitle
          tag="业务模块"
          title="十二大模块，按需组合"
          description="从订单到溯源，从计划到工资，各业务模块独立可扩展"
          :center="true"
        />
        <div class="module-grid">
          <ScrollReveal v-for="(mod, index) in modules" :key="mod.name" :delay="index * 60">
            <div class="module-card">
              <div class="module-icon">{{ mod.icon }}</div>
              <div>
                <h4>{{ mod.name }}</h4>
                <p>{{ mod.desc }}</p>
              </div>
            </div>
          </ScrollReveal>
        </div>
      </div>
    </section>

    <!-- Deployment Comparison -->
    <section class="section">
      <div class="container">
        <SectionTitle
          tag="部署方式"
          title="SaaS 多租户 vs 私有化部署"
          description="根据您的需求选择最合适的交付方式"
          :center="true"
        />
        <div class="version-grid">
          <div class="version-card">
            <div class="version-header" style="background: linear-gradient(135deg, #2563eb, #1e40af)">
              <div class="version-icon">☁️</div>
              <h3>SaaS 多租户</h3>
              <p class="version-subtitle">租户隔离 · 套餐计费 · 域名绑定</p>
            </div>
            <div class="version-body">
              <ul>
                <li>多租户数据隔离，独立管理后台</li>
                <li>租户套餐资源限制（管理员/用户数）</li>
                <li>订单购买、续费、升级自动化</li>
                <li>租户独立域名绑定</li>
                <li>平台方集中运维升级</li>
              </ul>
              <router-link to="/advantages" class="version-btn" style="background: #2563eb">
                查看多租户能力
              </router-link>
            </div>
          </div>
          <div class="version-card featured">
            <div class="version-header" style="background: linear-gradient(135deg, #10b981, #059669)">
              <div class="version-icon">🏠</div>
              <h3>私有化部署</h3>
              <p class="version-subtitle">数据自主可控 · 一键交付</p>
            </div>
            <div class="version-body">
              <ul>
                <li>数据 100% 私有化，内网可用</li>
                <li>ThinkPHP 8 + MySQL 轻量架构</li>
                <li>安装向导，分步完成部署</li>
                <li>RBAC 权限体系开箱即用</li>
                <li>可深度二次开发</li>
              </ul>
              <router-link to="/deploy" class="version-btn" style="background: #10b981">
                查看部署文档
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <ScrollReveal>
            <h2>准备好让车间生产数字化了吗？</h2>
            <p>多租户 SaaS 即开即用，或私有化部署数据自主可控</p>
            <div class="cta-actions">
              <router-link to="/features" class="btn btn-primary btn-large">查看功能</router-link>
              <router-link to="/deploy" class="btn btn-secondary btn-large">私有部署</router-link>
            </div>
          </ScrollReveal>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { useHead } from '@unhead/vue'
import SectionTitle from '@/components/SectionTitle.vue'
import FeatureCard from '@/components/FeatureCard.vue'
import ScrollReveal from '@/components/ScrollReveal.vue'
import CounterUp from '@/components/CounterUp.vue'

useHead({
  title: '辰科ThinkMES - 多租户 MES 制造执行系统 | 私有化部署',
  meta: [
    { name: 'description', content: '辰科ThinkMES 基于 ThinkPHP 8 的多租户 MES 制造执行系统，覆盖订单、BOM、排产、质检、库存、采购、工资核算全流程，支持多租户隔离与私有化部署。' }
  ]
})

const stats = [
  { value: 12, label: '核心业务模块', suffix: '' },
  { value: 15, label: '业务数据表', suffix: '+' },
  { value: 100, label: '租户数据隔离', suffix: '%' },
  { value: 7, label: '小时级交付部署', suffix: '' }
]

const features = [
  {
    icon: '📋',
    title: '订单管理',
    description: '创建、编辑、跟踪订单，支持批量导入，订单物料自动计算',
    features: ['批量导入', '物料需求计算', '采购申请联动'],
    color: '#2563eb'
  },
  {
    icon: '🧩',
    title: '产品与 BOM',
    description: '产品库 + 物料清单 + 工艺路线配置，支撑完整制造结构',
    features: ['产品型号管理', 'BOM 明细审核', '工艺路线'],
    color: '#10b981'
  },
  {
    icon: '🗓️',
    title: '生产计划',
    description: '排产、工序分配、产能负荷分析，让生产安排井井有条',
    features: ['智能排产', '工序分配', '产能分析'],
    color: '#f59e0b'
  },
  {
    icon: '✅',
    title: '质检管理',
    description: '质检标准、质检记录、不合格品处理，保障出厂质量',
    features: ['质检标准', '记录追溯', '不合格处理'],
    color: '#8b5cf6'
  },
  {
    icon: '📦',
    title: '库存管理',
    description: '入库/出库/盘点、安全库存预警，物料账实一致',
    features: ['出入库管理', '库存盘点', '安全预警'],
    color: '#ec4899'
  },
  {
    icon: '📊',
    title: '数据大屏',
    description: '产量趋势、良率分析、实时看板，管理决策有据可依',
    features: ['产量趋势', '良率分析', '实时看板'],
    color: '#06b6d4'
  }
]

const modules = [
  { icon: '📋', name: '订单管理', desc: '创建/编辑/跟踪，批量导入' },
  { icon: '🧩', name: '产品与 BOM', desc: '产品库 + 物料清单 + 工艺路线' },
  { icon: '🗓️', name: '生产计划', desc: '排产、工序分配、产能分析' },
  { icon: '✅', name: '质检管理', desc: '质检标准、记录、不合格处理' },
  { icon: '📦', name: '库存管理', desc: '出入库、盘点、安全库存预警' },
  { icon: '🛒', name: '采购管理', desc: '采购单、供应商、采购对账' },
  { icon: '💰', name: '工资核算', desc: '计件/计时工资、工资条统计' },
  { icon: '📊', name: '数据大屏', desc: '产量趋势、良率分析、实时看板' },
  { icon: '🔍', name: '溯源码', desc: '一物一码，扫码查全生产链路' },
  { icon: '📱', name: '小程序', desc: '员工扫码报工、查看工资' },
  { icon: '🏢', name: '多租户', desc: '数据隔离、套餐管理、域名绑定' },
  { icon: '🧰', name: '应用中心', desc: '按需安装扩展模块' }
]
</script>

<style lang="scss" scoped>
// Hero
.hero {
  position: relative;
  padding: 140px 0 80px;
  overflow: hidden;
  min-height: 90vh;
  display: flex;
  align-items: center;
}

.hero-bg {
  position: absolute;
  inset: 0;
  z-index: 0;

  .hero-grid {
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba($primary, 0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba($primary, 0.03) 1px, transparent 1px);
    background-size: 60px 60px;
  }

  .hero-glow {
    position: absolute;
    top: -200px;
    right: -200px;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba($primary, 0.08) 0%, transparent 70%);
    border-radius: 50%;
  }
}

.hero .container {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: $spacing-3xl;
  align-items: center;

  @include mobile {
    grid-template-columns: 1fr;
  }
}

.hero-content {
  .hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba($primary, 0.08);
    border: 1px solid rgba($primary, 0.15);
    border-radius: $radius-full;
    font-size: 14px;
    color: $primary;
    font-weight: 500;
    margin-bottom: $spacing-lg;

    .badge-dot {
      width: 8px;
      height: 8px;
      background: $primary;
      border-radius: 50%;
      animation: pulse 2s infinite;
    }
  }

  h1 {
    font-size: 3.2rem;
    font-weight: 800;
    line-height: 1.15;
    margin-bottom: $spacing-lg;
    color: $text-primary;

    @include mobile {
      font-size: 2.3rem;
    }
  }

  .gradient-text {
    @include gradient-text($primary, #8b5cf6);
  }

  .hero-desc {
    font-size: 1.125rem;
    color: $text-secondary;
    line-height: 1.8;
    margin-bottom: $spacing-xl;
  }

  .hero-actions {
    display: flex;
    gap: $spacing-md;
    flex-wrap: wrap;
  }
}

.hero-visual {
  @include mobile {
    display: none;
  }
}

.hero-card {
  background: $bg-white;
  border-radius: $radius-xl;
  box-shadow: $shadow-xl;
  overflow: hidden;
  border: 1px solid $border-light;

  .card-header {
    display: flex;
    align-items: center;
    gap: $spacing-md;
    padding: $spacing-md $spacing-lg;
    background: $gray-50;
    border-bottom: 1px solid $border-light;

    .card-dots {
      display: flex;
      gap: 6px;

      span {
        width: 10px;
        height: 10px;
        border-radius: 50%;

        &:nth-child(1) { background: #ef4444; }
        &:nth-child(2) { background: #f59e0b; }
        &:nth-child(3) { background: #10b981; }
      }
    }

    .card-title {
      font-size: 13px;
      color: $text-muted;
    }
  }

  .card-body {
    padding: $spacing-lg;
  }

  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: $spacing-md;
    margin-bottom: $spacing-lg;

    .kpi {
      padding: $spacing-md;
      background: $gray-50;
      border-radius: $radius-md;

      .kpi-label {
        display: block;
        font-size: 12px;
        color: $text-muted;
        margin-bottom: 4px;
      }

      .kpi-value {
        font-size: 1.4rem;
        font-weight: 800;
        color: $primary;
        font-family: $font-display;

        em {
          font-size: 0.8rem;
          font-style: normal;
          color: $text-muted;
          font-weight: 400;
          margin-left: 2px;
        }
      }
    }
  }

  .flow-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;

    .flow-step {
      padding: 6px 10px;
      border-radius: $radius-sm;
      background: $gray-100;
      color: $text-muted;
      font-size: 12px;
      white-space: nowrap;

      &.done {
        background: rgba($success, 0.12);
        color: $success;
      }

      &.active {
        background: $primary;
        color: white;
      }
    }

    .flow-arrow {
      width: 14px;
      height: 2px;
      background: $border-dark;
      flex-shrink: 0;
      position: relative;

      &::after {
        content: '';
        position: absolute;
        right: 0;
        top: -3px;
        border-left: 4px solid $border-dark;
        border-top: 4px solid transparent;
        border-bottom: 4px solid transparent;
      }
    }
  }
}

// Stats
.stats-section {
  padding: $spacing-3xl 0;
  background: $bg-white;
  border-bottom: 1px solid $border-light;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: $spacing-xl;
  text-align: center;

  @include mobile {
    grid-template-columns: repeat(2, 1fr);
    gap: $spacing-lg;
  }
}

.stat-item {
  .stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: $primary;
    font-family: $font-display;

    @include mobile {
      font-size: 2rem;
    }
  }

  .stat-label {
    font-size: 0.95rem;
    color: $text-secondary;
    margin-top: 4px;
  }
}

// Features
.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: $spacing-lg;

  @include tablet {
    grid-template-columns: repeat(2, 1fr);
  }

  @include mobile {
    grid-template-columns: 1fr;
  }
}

// Modules
.module-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: $spacing-md;

  @include tablet {
    grid-template-columns: repeat(2, 1fr);
  }

  @include mobile {
    grid-template-columns: 1fr;
  }
}

.module-card {
  display: flex;
  align-items: center;
  gap: $spacing-md;
  padding: $spacing-md;
  background: $bg-white;
  border-radius: $radius-md;
  border: 1px solid $border-light;
  transition: all $transition-fast;

  &:hover {
    box-shadow: $shadow-md;
    border-color: transparent;
    transform: translateY(-2px);
  }

  .module-icon {
    font-size: 24px;
    width: 44px;
    height: 44px;
    border-radius: $radius-md;
    background: rgba($primary, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  h4 {
    font-size: 0.95rem;
    font-weight: 600;
    color: $text-primary;
    margin-bottom: 2px;
  }

  p {
    font-size: 0.8rem;
    color: $text-muted;
    margin: 0;
  }
}

// Version Comparison
.version-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: $spacing-2xl;
  max-width: 900px;
  margin: 0 auto;

  @include mobile {
    grid-template-columns: 1fr;
  }
}

.version-card {
  border-radius: $radius-xl;
  overflow: hidden;
  border: 1px solid $border-light;
  transition: all $transition-normal;

  &:hover {
    transform: translateY(-4px);
    box-shadow: $shadow-lg;
  }

  &.featured {
    border-color: $success;
    box-shadow: 0 0 0 1px $success, $shadow-md;
  }
}

.version-header {
  padding: $spacing-xl;
  color: white;
  text-align: center;

  .version-icon {
    font-size: 48px;
    margin-bottom: $spacing-md;
  }

  h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: $spacing-xs;
    color: white;
  }

  .version-subtitle {
    font-size: 0.9rem;
    opacity: 0.9;
    margin: 0;
  }
}

.version-body {
  padding: $spacing-xl;
  background: $bg-white;

  ul {
    list-style: none;
    padding: 0;
    margin: 0 0 $spacing-xl;

    li {
      padding: $spacing-sm 0;
      color: $text-secondary;
      display: flex;
      align-items: center;
      gap: $spacing-sm;

      &::before {
        content: '✓';
        color: $success;
        font-weight: 700;
      }
    }
  }
}

.version-btn {
  display: block;
  text-align: center;
  padding: 12px 24px;
  color: white;
  border-radius: $radius-md;
  font-weight: 600;
  text-decoration: none;
  transition: all $transition-fast;

  &:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: $shadow-md;
  }
}

// CTA
.cta-section {
  padding: $spacing-4xl 0;
  background: linear-gradient(135deg, $dark-900 0%, $dark-800 100%);
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba($primary, 0.15) 0%, transparent 70%);
    border-radius: 50%;
  }
}

.cta-content {
  text-align: center;
  position: relative;
  z-index: 1;

  h2 {
    font-size: 2.5rem;
    color: $text-white;
    margin-bottom: $spacing-md;

    @include mobile {
      font-size: 2rem;
    }
  }

  p {
    font-size: 1.125rem;
    color: $gray-400;
    margin-bottom: $spacing-xl;
  }

  .cta-actions {
    display: flex;
    justify-content: center;
    gap: $spacing-md;
    flex-wrap: wrap;
  }
}
</style>
