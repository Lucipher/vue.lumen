<template>
  <div class="app-container calendar-list-container">
    <div class="filter-container">
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" class="filter-item" placeholder="姓名" v-model="listQuery.keyword">
      </el-input>

      <el-button class="filter-item" type="primary" icon="search" @click="handleFilter">搜索</el-button>
      <el-button class="filter-item" style="margin-left: 10px;" @click="handleCreate" type="primary" icon="edit">添加</el-button>
    </div>

    <el-table  :key='tableKey' :data="list" v-loading.body="listLoading" border fit highlight-current-row style="width: 100%">
      <el-table-column type="expand">
        <template scope="scope">
          <el-form inline class="demo-table-expand">
            <el-form-item label="QQ">
              <span>{{ scope.row.qq }}</span>
            </el-form-item>
            <el-form-item label="微信">
              <span>{{ scope.row.wechat_no }}</span>
            </el-form-item>
            <el-form-item label="备注">
              <span>{{ scope.row.desc }}</span>
            </el-form-item>
          </el-form>
        </template>
      </el-table-column>

      <el-table-column align="center" label="序号" width="65">
        <template scope="scope">
          <span>{{scope.row.id}}</span>
        </template>
      </el-table-column>

      <el-table-column min-width="220px" align="center" label="姓名">
        <template scope="scope">
          <span class="link-type" @click="handleUpdate(scope.row)">{{scope.row.real_name}}</span>
        </template>
      </el-table-column>

      <el-table-column width="100px" align="center" label="绑定状态">
        <template scope="scope">
          <el-tag :type="scope.row.is_bind | bindFilter">{{scope.row.is_bind | bindParse}}</el-tag>
        </template>
      </el-table-column>

      <el-table-column width="100px" align="center" label="是否禁用">
        <template scope="scope">
          <el-tag :type="scope.row.is_disabled | statusFilter">{{scope.row.is_disabled | statusParse}}</el-tag>
        </template>
      </el-table-column>

      <el-table-column width="200px" align="center" label="电话">
        <template scope="scope">
          <span>{{scope.row.cellphone}}</span>
        </template>
      </el-table-column>

      <el-table-column width="100px" align="center" label="性别">
        <template scope="scope">
          <span>{{scope.row.gender | genderParse}}</span>
        </template>
      </el-table-column>

      <el-table-column min-width="200px" align="center" label="公司名称">
        <template scope="scope">
          <span>{{scope.row.company_name}}</span>
        </template>
      </el-table-column>

      <el-table-column min-width="200px" align="center" label="描述">
        <template scope="scope">
          <span>{{scope.row.desc}}</span>
        </template>
      </el-table-column>

      <el-table-column  align="center" label="操作" width="100">
        <template scope="scope">
          <el-button v-if="scope.row.status!='deleted'" size="small" type="danger" @click="del(scope.row)">删除
            </el-button>
        </template>
      </el-table-column>
    </el-table>
    <div v-show="!listLoading" class="pagination-container">
      <el-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="listQuery.page" :page-sizes="[20]"
        :page-size="listQuery.limit" layout="total, sizes, prev, pager, next, jumper" :total="pagination.total">
      </el-pagination>
    </div>
    <el-dialog :title="textMap[dialogStatus]" :visible.sync="dialogFormVisible" :top="dialogTop">
      <el-form class="small-space" :model="temp" label-position="right" label-width="76px" style='width:400px; margin-left:50px;'>
        <el-form-item label="姓名">
          <el-input class="input250" v-model="temp.real_name" placeholder="姓名"></el-input>
        </el-form-item>

        <el-form-item label="公司名称">
          <el-input class="input250" v-model="temp.company_name" placeholder="公司名称"></el-input>
        </el-form-item>

        <el-form-item label="手机号码">
          <el-input class="input150" v-model="temp.cellphone" placeholder="手机"></el-input>
        </el-form-item>

        <el-form-item label="备注">
          <el-input type="textarea" :autosize="{ minRows: 2, maxRows: 2}" v-model="temp.desc"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">取 消</el-button>
        <el-button :loading="createLoading" v-if="dialogStatus=='create'" type="primary" @click="create">确 定</el-button>
        <el-button :loading="updateLoading" v-else type="primary" @click="update">更 新</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    getManorOwners,
    createManorOwner,
    updateManorOwner,
    changeManorOwnerStatus,
    deleteManorOwner
  } from '@/api/users'

  import { parseTime } from '@/utils'
  import { MessageBox } from 'element-ui'

  export default {
    data() {
      return {
        dialogTop: '5%',
        createLoading: false,
        updateLoading: false,
        list: [],           // 列表数据
        pagination: {       // 分页信息
        },
        listLoading: false, // 列表加载状态
        listQuery: { // 过滤条件
          page: 1,
          limit: 20,
          keyword: '',
          type: 1,
          sort: '+id'
        },
        tableKey: 0,
        dialogFormVisible: false,
        dialogStatus: '',
        textMap: {
          update: '编辑',
          create: '创建'
        },
        temp: {
          id: undefined,
          real_name: '',
          gender: 'unkown',
          is_bind: 0,
          cellphone: '',
          company_name: '',
          desc: ''
        },
        sortOptions: [{ label: '按ID升序列', key: '+id' }, { label: '按ID降序', key: '-id' }]
      }
    },
    created() { // 组件被挂载的时候执行
      this.getList()
    },
    mounted() {
    },
    components: {
    },
    filters: {
      bindFilter(s) { // 绑定状态过滤器
        const bind = {
          '1': 'success',
          '0': 'danger'
        }
        return bind[s]
      },
      bindParse(s) { // 绑定状态解析器
        const bind = {
          '0': '未绑定',
          '1': '已绑定'
        }
        return bind[s]
      },
      statusFilter(s) { // 用户状态过滤器
        const status = {
          '1': 'success',
          '0': 'danger'
        }
        return status[s]
      },
      statusParse(s) { // 用户状态解析器
        const status = {
          '0': '启用',
          '1': '禁用'
        }
        return status[s]
      },
      genderParse(s) { // 性别解析器
        const genders = {
          'male': '男',
          'female': '女',
          'unkown': '未知'
        }
        return genders[s]
      }
    },
    methods: {
      getList() { // 获取用户列表
        this.loading = true
        getManorOwners(this.listQuery).then(res => {
          this.list = res.data.data
          this.pagination = res.data.meta.pagination
          this.listLoading = false
        })
      },
      handleFilter() { // 筛选查询处理
        this.getList()
      },
      handleCreate() {
        this.resetTemp()
        this.dialogStatus = 'create'
        this.dialogFormVisible = true
      },
      handleUpdate(row) {
        this.temp = Object.assign({}, row)
        this.temp.selectedDept = [this.temp.parent_id, this.temp.dept_id]
        console.log(row)
        this.dialogStatus = 'update'
        this.dialogFormVisible = true
      },
      handleChange(v) {
        console.log(v)
      },
      handleDelete(row) {
        this.$notify({
          title: '成功',
          message: '删除成功',
          type: 'success',
          duration: 2000
        })
        const index = this.list.indexOf(row)
        this.list.splice(index, 1)
      },
      handleSizeChange(val) {
        this.listQuery.limit = val
        this.getList()
      },
      handleCurrentChange(val) {
        this.listQuery.page = val
        this.getList()
      },
      del(row) {
        MessageBox.confirm('确认删除, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          deleteManorOwner({ id: row.id }).then(response => {
            const {
              status
            } = response
            if (status === 204) {
              const index = this.list.indexOf(row)
              this.list.splice(index, 1)
            }
          })
        }).catch(() => {
        })
      },
      create() {
        this.createLoading = true
        createManorOwner(this.temp).then(response => {
          const {
            status
          } = response
          if (status === 201) {
            this.dialogFormVisible = false
            this.getList()
          }
          this.createLoading = false
        })
      },
      update() {
        this.updateLoading = true
        updateManorOwner(this.temp).then(response => {
          const {
            status
          } = response
          if (status === 204) {
            this.dialogFormVisible = false
            this.getList()
          }
          this.updateLoading = false
        })
      },
      toggleStatus(row) {
        changeManorOwnerStatus({ id: row.id }).then(response => {
          const {
            status
          } = response
          if (status === 204) {
            this.getList()
          }
        })
      },
      resetTemp() {
        this.temp = {
          id: undefined,
          real_name: '',
          gender: 'unkown',
          is_bind: 0,
          cellphone: '',
          company_name: '',
          desc: ''
        }
      },
      formatJson(filterVal, jsonData) {
        return jsonData.map(v => filterVal.map(j => {
          if (j === 'timestamp') {
            return parseTime(v[j])
          } else {
            return v[j]
          }
        }))
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
  .input100 { width:100px; }
  .input150 { width:150px; }
  .input200 { width:200px; }
  .input250 { width:250px; }
  .input500 { width:500px; }

  .demo-table-expand {
    font-size: 0;
  }
  .demo-table-expand label {
    width: 90px;
    color: #99a9bf;
  }
  .demo-table-expand .el-form-item {
    margin-right: 0;
    margin-bottom: 0;
    width: 33%;
  }
</style>
