<template>
  <div class="advantages-page">
    <section class="page-hero">
      <div class="container">
        <div class="page-hero-content">
          <span class="hero-tag">技术优势</span>
          <h1>为什么选择辰科ThinkMES</h1>
          <p>面向中小型加工厂的 MES 系统，既要功能全面，也要轻量易用、部署简单</p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="advantages-grid">
          <ScrollReveal v-for="(adv, index) in advantages" :key="adv.title" :delay="index * 100">
            <div class="adv-card">
              <div class="adv-icon" :style="{ background: adv.bg }">{{ adv.icon }}</div>
              <h3>{{ adv.title }}</h3>
              <p>{{ adv.description }}</p>
            </div>
          </ScrollReveal>
        </div>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container">
        <SectionTitle
          tag="技术栈"
          title="轻量成熟的技术底座"
          description="ThinkPHP 8 + MySQL + Layui，成熟稳定、上手快、可深度定制"
          :center="true"
        />
        <div class="stack-grid">
          <div class="stack-item" v-for="tech in techStack" :key="tech.name">
            <div class="stack-icon">{{ tech.icon }}</div>
            <div>
              <strong>{{ tech.name }}</strong>
              <span>{{ tech.desc }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <SectionTitle
          tag="对比优势"
          title="ThinkMES 的核心竞争力"
          description="多租户 + 应用中心 + 全程追溯，真正适合中小型加工厂的一体化方案"
          :center="true"
        />
        <div class="compare-grid">
          <div class="compare-card vs">
            <h3>传统 Excel + 纸质管理</h3>
            <ul>
              <li class="no">数据分散，查找困难</li>
              <li class="no">排产靠经验，产能难把控</li>
              <li class="no">质量不可追溯</li>
              <li class="no">工资核算繁琐易错</li>
              <li class="no">无法支撑多厂/多租户</li>
            </ul>
          </div>
          <div class="compare-card win">
            <div class="win-badge">推荐</div>
            <h3>辰科ThinkMES</h3>
            <ul>
              <li class="yes">全流程数字化，数据实时可见</li>
              <li class="yes">智能排产 + 产能负荷分析</li>
              <li class="yes">一物一码全程追溯</li>
              <li class="yes">计件/计时工资自动核算</li>
              <li class="yes">多租户隔离 + 应用中心扩展</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>告别纸质流转与 Excel 表格</h2>
          <p>让生产管理更简单、更透明、更可控</p>
          <div class="cta-actions">
            <router-link to="/features" class="btn btn-primary btn-large">查看功能</router-link>
            <router-link to="/deploy" class="btn btn-secondary btn-large">私有部署</router-link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { useHead } from '@unhead/vue'
import SectionTitle from '@/components/SectionTitle.vue'
import ScrollReveal from '@/components/ScrollReveal.vue'

useHead({
  title: '辰科ThinkMES - 技术优势 | 多租户 MES 制造执行系统',
  meta: [
    { name: 'description', content: '辰科ThinkMES 基于 ThinkPHP 8 + MySQL + Layui 轻量成熟技术栈，支持多租户隔离、应用中心扩展、一物一码全程追溯，适合中小型加工厂私有化部署。' }
  ]
})

const advantages = [
  {
    icon: '🏢',
    title: '多租户架构',
    description: '数据隔离 + 资源限制 + 套餐计费 + 域名绑定，一套系统即可服务多家企业，天然适合 SaaS 平台化运营。',
    bg: 'rgba(37, 99, 235, 0.1)'
  },
  {
    icon: '🧰',
    title: '应用中心扩展',
    description: '基于可复用底座，MES、CRM、AI、人事、财务等应用按需安装，业务随需成长，不用推倒重来。',
    bg: 'rgba(16, 185, 129, 0.1)'
  },
  {
    icon: '🚀',
    title: '轻量快速部署',
    description: 'ThinkPHP 8 + MySQL 轻量架构，安装向导分步完成，几小时内即可交付使用，资源占用低。',
    bg: 'rgba(245, 158, 11, 0.1)'
  },
  {
    icon: '🔍',
    title: '全程质量追溯',
    description: '质检记录 + 一物一码溯源码，每件产品的生产链路清晰可查，质量责任一追到底。',
    bg: 'rgba(139, 92, 246, 0.1)'
  },
  {
    icon: '📱',
    title: '移动办公',
    description: '小程序扫码报工、查看工资，工人用手机即可完成日常操作，无需培训成本。',
    bg: 'rgba(236, 72, 153, 0.1)'
  },
  {
    icon: '🔒',
    title: '数据自主可控',
    description: '私有化部署，数据 100% 掌握在自己手中；RBAC 精细权限 + 操作日志，安全合规。',
    bg: 'rgba(6, 182, 212, 0.1)'
  }
]

const techStack = [
  { name: 'ThinkPHP 8', icon: '🐘', desc: '成熟稳定 PHP 框架' },
  { name: 'MySQL 5.7+', icon: '🗄️', desc: '主流关系型数据库' },
  { name: 'Layui', icon: '🎨', desc: '后台管理 UI 框架' },
  { name: 'PHP 8.0+', icon: '⚡', desc: '高性能运行环境' },
  { name: '小程序', icon: '📱', desc: '员工移动端办公' },
  { name: 'Think 模板', icon: '🧩', desc: '服务端模板渲染' }
]
</script>

<style lang="scss" scoped>
.page-hero {
  padding: 140px 0 60px;
  background: linear-gradient(180deg, $gray-50 0%, $bg-white 100%);
  border-bottom: 1px solid $border-light;
}

.page-hero-content {
  text-align: center;
  max-width: 720px;
  margin: 0 auto;

  .hero-tag {
    display: inline-block;
    padding: 6px 16px;
    background: rgba($primary, 0.1);
    color: $primary;
    font-size: 13px;
    font-weight: 600;
    border-radius: $radius-full;
    margin-bottom: $spacing-md;
  }

  h1 {
    font-size: 2.5rem;
    font-weight: 800;
    color: $text-primary;
    margin-bottom: $spacing-md;

    @include mobile {
      font-size: 1.9rem;
    }
  }

  p {
    font-size: 1.125rem;
    color: $text-secondary;
    margin: 0;
  }
}

.advantages-grid {
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

.adv-card {
  background: $bg-white;
  border-radius: $radius-lg;
  padding: $spacing-xl;
  border: 1px solid $border-light;
  transition: all $transition-normal;

  &:hover {
    transform: translateY(-4px);
    box-shadow: $shadow-lg;
    border-color: transparent;
  }

  .adv-icon {
    width: 56px;
    height: 56px;
    border-radius: $radius-md;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: $spacing-md;
  }

  h3 {
    font-size: 1.25rem;
    color: $text-primary;
    margin-bottom: $spacing-sm;
  }

  p {
    color: $text-secondary;
    font-size: 0.95rem;
    line-height: 1.7;
    margin: 0;
  }
}

.stack-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: $spacing-md;
  max-width: 900px;
  margin: 0 auto;

  @include mobile {
    grid-template-columns: 1fr;
  }
}

.stack-item {
  display: flex;
  align-items: center;
  gap: $spacing-md;
  padding: $spacing-lg;
  background: $bg-white;
  border-radius: $radius-md;
  border: 1px solid $border-light;

  .stack-icon {
    font-size: 28px;
    width: 48px;
    text-align: center;
  }

  strong {
    display: block;
    font-size: 0.95rem;
    color: $text-primary;
  }

  span {
    font-size: 0.8rem;
    color: $text-muted;
  }
}

.compare-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: $spacing-2xl;
  max-width: 900px;
  margin: 0 auto;

  @include mobile {
    grid-template-columns: 1fr;
  }
}

.compare-card {
  border-radius: $radius-xl;
  padding: $spacing-xl;
  border: 1px solid $border-light;
  background: $bg-white;
  position: relative;

  &.win {
    border-color: $success;
    box-shadow: 0 0 0 1px $success, $shadow-md;
  }

  .win-badge {
    position: absolute;
    top: -12px;
    left: $spacing-xl;
    background: $success;
    color: white;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 14px;
    border-radius: $radius-full;
  }

  h3 {
    font-size: 1.25rem;
    color: $text-primary;
    margin-bottom: $spacing-lg;
    text-align: center;
  }

  ul {
    list-style: none;
    padding: 0;
    margin: 0;

    li {
      padding: $spacing-sm 0;
      color: $text-secondary;
      display: flex;
      align-items: center;
      gap: $spacing-sm;
      font-size: 0.95rem;

      &.no::before {
        content: '✕';
        color: $error;
        font-weight: 700;
      }

      &.yes::before {
        content: '✓';
        color: $success;
        font-weight: 700;
      }
    }
  }
}

.cta-section {
  padding: $spacing-4xl 0;
  background: linear-gradient(135deg, $dark-900 0%, $dark-800 100%);
}

.cta-content {
  text-align: center;

  h2 {
    font-size: 2.5rem;
    color: $text-white;
    margin-bottom: $spacing-md;

    @include mobile {
      font-size: 1.9rem;
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
