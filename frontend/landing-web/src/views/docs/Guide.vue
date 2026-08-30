<template>
  <div class="guide-page">
    <section class="page-hero">
      <div class="container">
        <div class="page-hero-content">
          <span class="hero-tag">使用指南</span>
          <h1>ThinkMES 使用入门</h1>
          <p>从登录后台到开启第一个生产订单，快速上手</p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="guide-content">
          <div class="guide-nav">
            <div
              class="guide-nav-item"
              v-for="(sec, i) in sections"
              :key="sec.title"
              :class="{ active: activeSection === i }"
              @click="activeSection = i"
            >
              <span class="nav-icon">{{ sec.icon }}</span>
              <span>{{ sec.title }}</span>
            </div>
          </div>

          <div class="guide-body">
            <template v-for="(sec, i) in sections" :key="sec.title">
              <div v-show="activeSection === i" class="guide-section">
                <h2>{{ sec.title }}</h2>
                <div class="guide-text">
                  <p v-for="(p, j) in sec.paragraphs" :key="j">{{ p }}</p>
                </div>
                <div class="guide-tip" v-if="sec.tip">
                  <span class="tip-icon">💡</span>
                  <p>{{ sec.tip }}</p>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>需要更详细的文档？</h2>
          <p>完整使用文档与技术支持随时为您提供</p>
          <div class="cta-actions">
            <a :href="urls.contact" target="_blank" rel="noopener" class="btn btn-primary btn-large">联系我们</a>
            <router-link to="/deploy" class="btn btn-secondary btn-large">查看部署文档</router-link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useHead } from '@unhead/vue'
import { SITE_URLS } from '@/config/site'

const urls = SITE_URLS
const activeSection = ref(0)

useHead({
  title: '辰科ThinkMES - 使用指南 | 多租户 MES 制造执行系统',
  meta: [
    { name: 'description', content: '辰科ThinkMES 使用入门指南：系统登录、基础配置、订单管理、排产生产、质检追溯、工资核算快速上手。' }
  ]
})

const sections = [
  {
    icon: '🔑',
    title: '系统登录与后台入口',
    paragraphs: [
      '部署完成后，使用安装向导中设置的管理员账号登录。系统采用随机后台入口机制，每次登录可生成随机后台地址，提升安全性。',
      '登录后即可看到 RBAC 权限体系下的后台菜单，不同角色看到的功能菜单自动过滤，只显示有权限的模块。'
    ],
    tip: '生产环境建议定期修改管理员密码，并为不同员工分配独立账号与角色权限。'
  },
  {
    icon: '🏢',
    title: '租户与套餐配置',
    paragraphs: [
      '在后台「租户套餐」中添加基础版、标准版等套餐，配置资源限制（最大管理员数、最大用户数）与默认有效期。',
      '在「租户管理」中创建租户并选择套餐，系统会自动初始化租户管理员账号。支持为每个租户绑定独立访问域名。'
    ],
    tip: '租户到期前可一键续费，或升级到更高套餐，系统自动延长到期时间。'
  },
  {
    icon: '📋',
    title: '创建订单',
    paragraphs: [
      '进入「订单管理」创建订单，选择客户、填写产品与数量。系统根据产品 BOM 自动计算物料需求。',
      '支持批量导入订单，快速完成历史订单建档。订单状态全程可跟踪，从待排产到生产、质检、出库。'
    ],
    tip: '订单创建后可在「采购申请」中一键生成采购单，物料需求自动衔接采购流程。'
  },
  {
    icon: '🗓️',
    title: '排产与生产报工',
    paragraphs: [
      '在「生产计划」中进行排产，分配工序与产能。结合工序工价配置，支持计件工资核算。',
      '车间员工通过小程序扫码报工，系统实时更新生产进度，同时自动计算计件工资。'
    ],
    tip: '报工数据是工资核算与产量统计的基础，建议员工每道工序完成后及时扫码报工。'
  },
  {
    icon: '✅',
    title: '质检与不合格处理',
    paragraphs: [
      '按质检标准进行检验，录入质检记录。不合格品进入处理流程，记录处理结果，形成质量闭环。',
      '质检记录与订单、产品绑定，支持全程质量追溯。'
    ],
    tip: '一物一码溯源码，扫码即可查询产品从原料到成品的完整生产链路。'
  },
  {
    icon: '💰',
    title: '工资核算',
    paragraphs: [
      '根据工序工价与员工报工记录，系统自动核算计件/计时工资，生成工资条与统计报表。',
      '员工可通过小程序查看自己的工资明细，透明公正。'
    ],
    tip: '可结合数据大屏查看产量趋势、良率分析，为薪酬与生产决策提供依据。'
  }
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

.guide-content {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: $spacing-2xl;
  max-width: 1000px;
  margin: 0 auto;
  align-items: start;

  @include mobile {
    grid-template-columns: 1fr;
  }
}

.guide-nav {
  position: sticky;
  top: 100px;
  display: flex;
  flex-direction: column;
  gap: $spacing-xs;
  background: $bg-white;
  border-radius: $radius-lg;
  padding: $spacing-md;
  border: 1px solid $border-light;

  @include mobile {
    position: static;
    flex-direction: row;
    flex-wrap: wrap;
  }
}

.guide-nav-item {
  display: flex;
  align-items: center;
  gap: $spacing-sm;
  padding: $spacing-sm $spacing-md;
  border-radius: $radius-sm;
  cursor: pointer;
  font-size: 0.9rem;
  color: $text-secondary;
  transition: all $transition-fast;

  &:hover {
    background: $gray-50;
    color: $text-primary;
  }

  &.active {
    background: rgba($primary, 0.1);
    color: $primary;
    font-weight: 600;
  }

  .nav-icon {
    font-size: 18px;
  }
}

.guide-body {
  background: $bg-white;
  border-radius: $radius-lg;
  padding: $spacing-2xl;
  border: 1px solid $border-light;
  min-height: 400px;

  @include mobile {
    padding: $spacing-xl;
  }
}

.guide-section {
  h2 {
    font-size: 1.5rem;
    color: $text-primary;
    margin-bottom: $spacing-lg;
  }
}

.guide-text {
  p {
    color: $text-secondary;
    font-size: 0.98rem;
    line-height: 1.9;
    margin-bottom: $spacing-md;
  }
}

.guide-tip {
  display: flex;
  gap: $spacing-md;
  padding: $spacing-md $spacing-lg;
  background: rgba($warning, 0.08);
  border-left: 3px solid $warning;
  border-radius: 0 $radius-md $radius-md 0;

  .tip-icon {
    font-size: 20px;
  }

  p {
    color: $text-secondary;
    font-size: 0.9rem;
    line-height: 1.7;
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
