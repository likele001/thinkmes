<template>
  <div class="features-page">
    <section class="page-hero">
      <div class="container">
        <div class="page-hero-content">
          <span class="hero-tag">核心功能</span>
          <h1>覆盖制造全流程的十二大模块</h1>
          <p>从订单接收到成品出库，从计划排产到追溯质检，ThinkMES 让每一个生产环节都清晰可控</p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="feature-list">
          <ScrollReveal v-for="(item, index) in featureItems" :key="item.title" :delay="index * 80">
            <div class="feature-row" :class="{ reverse: index % 2 === 1 }">
              <div class="feature-icon">{{ item.icon }}</div>
              <div class="feature-content">
                <h2>{{ item.title }}</h2>
                <p>{{ item.description }}</p>
                <ul>
                  <li v-for="point in item.points" :key="point">{{ point }}</li>
                </ul>
              </div>
            </div>
          </ScrollReveal>
        </div>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container">
        <SectionTitle
          tag="工作流"
          title="从接单到出库，全程数字化"
          description="ThinkMES 贯穿生产制造的核心环节，数据自动流转、状态实时可见"
          :center="true"
        />
        <div class="workflow">
          <div class="workflow-step" v-for="(step, i) in workflowSteps" :key="step.name">
            <div class="step-circle" :class="{ done: i < 5 }">
              <span>{{ step.icon }}</span>
            </div>
            <div class="step-name">{{ step.name }}</div>
            <div class="step-desc">{{ step.desc }}</div>
            <div v-if="i < workflowSteps.length - 1" class="step-line"></div>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>需要一款真正懂车间生产的 MES？</h2>
          <p>ThinkMES 专为中小型加工厂设计，开箱即用、按需扩展</p>
          <div class="cta-actions">
            <router-link to="/deploy" class="btn btn-primary btn-large">开始私有部署</router-link>
            <router-link to="/advantages" class="btn btn-secondary btn-large">了解技术优势</router-link>
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
  title: '辰科ThinkMES - 核心功能 | 多租户 MES 制造执行系统',
  meta: [
    { name: 'description', content: '辰科ThinkMES 覆盖订单管理、产品BOM、生产计划、质检、库存、采购、工资核算、数据大屏、溯源码、小程序、多租户、应用中心十二大模块。' }
  ]
})

const featureItems = [
  {
    icon: '📋',
    title: '订单管理',
    description: '从接单开始，订单全生命周期管理。创建、编辑、跟踪订单状态，支持批量导入，自动计算物料需求并联动采购申请。',
    points: ['创建/编辑/跟踪订单，全流程状态管理', '支持批量导入，快速建档', '订单物料需求自动计算，联动采购申请']
  },
  {
    icon: '🧩',
    title: '产品与 BOM',
    description: '完善的产品数据底座。产品库 + 产品型号 + 物料清单（BOM）+ 工艺路线，构建完整的制造数据结构。',
    points: ['产品库 + 型号管理', 'BOM 明细配置与审核', '工艺路线标准化']
  },
  {
    icon: '🗓️',
    title: '生产计划',
    description: '科学的排产与产能管理。工序分配、产能负荷分析，让生产安排高效有序，减少等待与浪费。',
    points: ['订单排产、工序分配', '产能负荷分析', '生产进度实时追踪']
  },
  {
    icon: '✅',
    title: '质检管理',
    description: '全流程质量管控。质检标准、质检记录、不合格品处理闭环，保障每一件出厂产品的质量。',
    points: ['质检标准配置', '质检记录与追溯', '不合格品处理流程']
  },
  {
    icon: '📦',
    title: '库存管理',
    description: '物料账实一致。入库、出库、盘点全管理，安全库存预警避免断料停产。',
    points: ['入库/出库/盘点管理', '安全库存自动预警', '库存流水全追溯']
  },
  {
    icon: '🛒',
    title: '采购管理',
    description: '物料供应保障。采购单、供应商管理、采购对账，与订单物料需求自动衔接。',
    points: ['采购单管理', '供应商档案', '采购对账']
  },
  {
    icon: '💰',
    title: '工资核算',
    description: '车间计件/计时工资核算。计件单价、计时工时，自动计算工资并生成工资条。',
    points: ['计件/计时工资核算', '工资条与统计报表', '员工扫码查询']
  },
  {
    icon: '📊',
    title: '数据大屏',
    description: '车间数据一屏掌握。产量趋势、良率分析、实时看板，管理决策有据可依。',
    points: ['产量趋势图表', '良率多维分析', '生产实时看板']
  },
  {
    icon: '🔍',
    title: '溯源码',
    description: '一物一码，全程可溯。每件产品绑定溯源码，扫码即可查询完整生产链路。',
    points: ['一物一码', '扫码追溯全链路', '生产履历留痕']
  },
  {
    icon: '📱',
    title: '小程序',
    description: '移动端便捷办公。员工扫码报工、查看工资，随时掌握生产进度。',
    points: ['员工扫码报工', '工资实时查询', '移动端消息提醒']
  },
  {
    icon: '🏢',
    title: '多租户',
    description: '平台化运营能力。租户数据隔离、套餐管理、域名绑定，一个系统服务多个企业。',
    points: ['租户数据完全隔离', '套餐资源限制', '独立域名绑定']
  },
  {
    icon: '🧰',
    title: '应用中心',
    description: '按需扩展，灵活组合。通过应用中心上传安装扩展模块，功能随业务增长。',
    points: ['应用包上传安装', '模块独立启用', '按需组合扩展']
  }
]

const workflowSteps = [
  { icon: '📨', name: '接单', desc: '创建订单，自动计算物料需求' },
  { icon: '🧩', name: '备料', desc: 'BOM 展开，采购申请联动' },
  { icon: '🗓️', name: '排产', desc: '工序分配，产能负荷分析' },
  { icon: '🏭', name: '生产', desc: '员工扫码报工，进度实时更新' },
  { icon: '✅', name: '质检', desc: '按标准检验，不合格闭环处理' },
  { icon: '📦', name: '出库', desc: '成品出库，溯源码绑定' },
  { icon: '📊', name: '分析', desc: '产量/良率数据大屏洞察' }
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

.feature-list {
  display: flex;
  flex-direction: column;
  gap: $spacing-2xl;
  max-width: 960px;
  margin: 0 auto;
}

.feature-row {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: $spacing-xl;
  align-items: start;
  padding: $spacing-2xl;
  background: $bg-white;
  border-radius: $radius-xl;
  border: 1px solid $border-light;
  transition: all $transition-normal;

  &:hover {
    box-shadow: $shadow-lg;
    border-color: transparent;
    transform: translateY(-2px);
  }

  .feature-icon {
    font-size: 56px;
    width: 100px;
    height: 100px;
    background: rgba($primary, 0.08);
    border-radius: $radius-lg;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .feature-content {
    h2 {
      font-size: 1.5rem;
      color: $text-primary;
      margin-bottom: $spacing-sm;
    }

    p {
      color: $text-secondary;
      font-size: 0.95rem;
      line-height: 1.8;
      margin-bottom: $spacing-md;
    }

    ul {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-wrap: wrap;
      gap: $spacing-sm;

      li {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: $gray-50;
        border-radius: $radius-full;
        font-size: 0.85rem;
        color: $text-secondary;

        &::before {
          content: '✓';
          color: $success;
          font-weight: 700;
        }
      }
    }
  }

  @include mobile {
    grid-template-columns: 1fr;
    padding: $spacing-xl;

    .feature-icon {
      width: 72px;
      height: 72px;
      font-size: 40px;
    }
  }
}

.workflow {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: $spacing-md;
  position: relative;
  max-width: 1100px;
  margin: 0 auto;

  @include tablet {
    grid-template-columns: repeat(4, 1fr);
  }

  @include mobile {
    grid-template-columns: repeat(2, 1fr);
  }
}

.workflow-step {
  position: relative;
  text-align: center;
  padding: $spacing-md;

  .step-circle {
    width: 64px;
    height: 64px;
    margin: 0 auto $spacing-md;
    border-radius: $radius-full;
    background: $bg-white;
    border: 2px solid $border-light;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    position: relative;
    z-index: 1;

    &.done {
      background: rgba($success, 0.1);
      border-color: $success;
    }
  }

  .step-name {
    font-weight: 600;
    color: $text-primary;
    font-size: 0.95rem;
    margin-bottom: 4px;
  }

  .step-desc {
    font-size: 0.8rem;
    color: $text-muted;
  }

  .step-line {
    position: absolute;
    top: 48px;
    left: 50%;
    width: 100%;
    height: 2px;
    background: $border-light;
    z-index: 0;

    @include mobile {
      display: none;
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
