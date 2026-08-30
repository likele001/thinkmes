<template>
  <div class="deploy-page">
    <section class="page-hero">
      <div class="container">
        <div class="page-hero-content">
          <span class="hero-tag">私有部署</span>
          <h1>ThinkMES 私有化部署</h1>
          <p>轻量架构 + 安装向导，几小时内即可完成部署上线</p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="deploy-content">
          <SectionTitle
            tag="环境要求"
            title="部署环境"
            description="ThinkPHP 8 轻量架构，对服务器要求不高"
          />
          <div class="env-grid">
            <div class="env-item" v-for="env in envs" :key="env.name">
              <div class="env-icon">{{ env.icon }}</div>
              <div>
                <strong>{{ env.name }}</strong>
                <span>{{ env.desc }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container">
        <SectionTitle
          tag="安装步骤"
          title="五步完成部署"
          description="从获取代码到正式上线，全过程清晰可循"
          :center="true"
        />
        <div class="steps">
          <div class="step" v-for="(step, i) in steps" :key="step.title">
            <div class="step-num">{{ String(i + 1).padStart(2, '0') }}</div>
            <h3>{{ step.title }}</h3>
            <p>{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="deploy-content">
          <SectionTitle
            tag="多租户"
            title="部署后可开启多租户运营"
            description="私有化部署后，同样支持平台化多租户运营"
          />
          <div class="saas-grid">
            <div class="saas-item" v-for="item in saasFeatures" :key="item.title">
              <div class="saas-icon">{{ item.icon }}</div>
              <h4>{{ item.title }}</h4>
              <p>{{ item.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>准备好部署 ThinkMES 了吗？</h2>
          <p>获取完整部署文档与技术指导</p>
          <div class="cta-actions">
            <a :href="urls.github" target="_blank" rel="noopener" class="btn btn-primary btn-large">GitHub 仓库</a>
            <a :href="urls.contact" target="_blank" rel="noopener" class="btn btn-secondary btn-large">联系我们</a>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { useHead } from '@unhead/vue'
import SectionTitle from '@/components/SectionTitle.vue'
import { SITE_URLS } from '@/config/site'

const urls = SITE_URLS

useHead({
  title: '辰科ThinkMES - 私有部署 | 多租户 MES 制造执行系统',
  meta: [
    { name: 'description', content: '辰科ThinkMES 私有化部署指南。ThinkPHP 8 + MySQL 轻量架构，环境要求低，安装向导分步部署，支持多租户运营。' }
  ]
})

const envs = [
  { icon: '🐘', name: 'PHP 8.0+', desc: '推荐 PHP 8.1 / 8.2' },
  { icon: '🗄️', name: 'MySQL 5.7+', desc: '主流关系型数据库' },
  { icon: '🧰', name: 'Composer', desc: 'PHP 依赖管理' },
  { icon: '🌐', name: 'Web 服务器', desc: 'Nginx / Apache 均可' }
]

const steps = [
  { title: '获取代码', desc: '从发布页下载安装包，或克隆 GitHub 仓库后使用打包脚本生成 base 包。' },
  { title: '配置 Web 目录', desc: '解压项目，将网站运行目录指向项目下的 public 目录。' },
  { title: '运行安装向导', desc: '浏览器访问 /install，按步骤完成环境检测、数据库配置、管理员设置。' },
  { title: '配置多租户', desc: '在后台添加租户套餐（基础版/标准版），创建租户并选择套餐，支持独立域名绑定。' },
  { title: '安装业务应用', desc: '登录后台进入应用中心，上传 MES/CRM/AI 等应用包安装，菜单自动显示。' }
]

const saasFeatures = [
  { icon: '🏢', title: '租户管理', desc: '租户列表、创建编辑、到期时间、状态管理' },
  { icon: '💳', title: '套餐计费', desc: '套餐配置、订单购买、续费升级自动化' },
  { icon: '🔒', title: '数据隔离', desc: 'tenant_id 全表隔离，租户间数据完全独立' },
  { icon: '🌐', title: '域名绑定', desc: '每个租户可绑定独立访问域名' }
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

.deploy-content {
  max-width: 960px;
  margin: 0 auto;
}

.env-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: $spacing-md;

  @include mobile {
    grid-template-columns: 1fr;
  }
}

.env-item {
  display: flex;
  align-items: center;
  gap: $spacing-md;
  padding: $spacing-lg;
  background: $bg-white;
  border-radius: $radius-md;
  border: 1px solid $border-light;

  .env-icon {
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
    font-size: 0.85rem;
    color: $text-muted;
  }
}

.steps {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: $spacing-lg;
  max-width: 1100px;
  margin: 0 auto;

  @include tablet {
    grid-template-columns: repeat(3, 1fr);
  }

  @include mobile {
    grid-template-columns: 1fr;
  }
}

.step {
  background: $bg-white;
  border-radius: $radius-lg;
  padding: $spacing-xl;
  border: 1px solid $border-light;
  position: relative;
  transition: all $transition-normal;

  &:hover {
    transform: translateY(-4px);
    box-shadow: $shadow-lg;
    border-color: transparent;
  }

  .step-num {
    font-size: 2.5rem;
    font-weight: 800;
    color: rgba($primary, 0.15);
    font-family: $font-display;
    margin-bottom: $spacing-md;
  }

  h3 {
    font-size: 1.1rem;
    color: $text-primary;
    margin-bottom: $spacing-sm;
  }

  p {
    font-size: 0.9rem;
    color: $text-secondary;
    line-height: 1.7;
    margin: 0;
  }
}

.saas-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: $spacing-md;

  @include mobile {
    grid-template-columns: 1fr;
  }
}

.saas-item {
  display: flex;
  align-items: flex-start;
  gap: $spacing-md;
  padding: $spacing-lg;
  background: $bg-white;
  border-radius: $radius-md;
  border: 1px solid $border-light;

  .saas-icon {
    font-size: 28px;
    width: 48px;
    text-align: center;
    flex-shrink: 0;
  }

  h4 {
    font-size: 1rem;
    color: $text-primary;
    margin-bottom: 4px;
  }

  p {
    font-size: 0.85rem;
    color: $text-muted;
    margin: 0;
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
