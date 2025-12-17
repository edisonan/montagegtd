<?php
namespace App\Admin\Controllers;

use App\Models\Code;
use App\Models\CodeHistory;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

/**
 * 代码管理
 *
 */
class CodeController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return Admin::content ( function (Content $content) {

            $content->header ( 'header' );
            $content->description ( 'description' );

            $content->body ( $this->grid () );
        } );
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        $model = Code::findOrFail($id);
        $view = view('codeview', [
            'id' => $model->id,
            'name' => $model->name,
            'type' => $model->type,
            'content' => $model->content,
            'status' => $model->status,
        ])->render();
        return $view;
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return Admin::content ( function (Content $content) use ($id) {

            $content->header ( 'header' );
            $content->description ( 'description' );

            $content->body ( $this->form ()->edit ( $id ) );
        } );
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return Admin::content ( function (Content $content) {

            $content->header ( 'header' );
            $content->description ( 'description' );

            $content->body ( $this->form () );
        } );
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Code::class, function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');

            $grid->id('Id');
            $grid->column('名称和类型')->display(function () {
                $typeText = '';
                $type = $this->type;
                $typeMap = array(1=>'php',2=>'html');
                if (isset($typeMap[$type])) {
                    $typeText = $typeMap[$type];
                }
                return '【' . $typeText . '】- <a href="/admin/codes/'.$this->id.'">'.$this->name.'</a> - <a target="_blank" href="/code/'.$this->id.'">查看</a>';
            });
//            $grid->content('代码内容')->limit(100);
            $grid->status('状态')->display(function ($value) {
                return $value == 1? '启用' : '禁用';
            });
            $grid->created_at('创建时间');
            $grid->updated_at('更新时间');

            $grid->actions(function ($actions) {
                $actions->disableDelete();
            });

        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Code::findOrFail($id));

        $show->id('Id');
        $show->name('名称');
        $show->type('类型');
        $show->content('代码内容');
        $show->status('状态');
        $show->created_at('创建时间');
        $show->updated_at('更新时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form ( Code::class, function (Form $form) {
            $form->text('name', '名称');
            $form->radio('type', '类型')->options(array(1=>'php',2=>'html'))->default(1);
            $form->textarea('content', '代码内容');
            $form->radio('status', '状态')->options(array(1=>'开启',2=>'关闭'))->default(1);
        });
    }

    public function getCode($id)
    {
        $model = Code::findOrFail($id);

        return response()->json([
            'code' => '9999',
            'message' => 'success',
            'data' => [
                'content' => $model->content,
                'status' => $model->status
            ]
        ]);
    }

    /**
     * Update code content
     */
    public function updateCode()
    {
        $id = request('id');
        $content = request('content');

        $model = Code::findOrFail($id);
        if ($model->content != $content) {
            $model->content = $content;
            $model->save();

            CodeHistory::create([
                'code_id' => $id,
                'content' => $content
            ]);
        }

        return response()->json([
            'code' => '9999',
            'message' => 'success',
            'data' => []
        ]);
    }

    /**
     * Generate code via AI
     */
    public function generateCode()
    {
        $prompt = request('prompt');


        if (empty($prompt)) {
            return response()->json([
                'code' => '9999',
                'message' => 'success',
                'data' => [
                    'content' => "// 请提供更详细的代码需求描述\n// 例如：创建一个计算两个数之和的函数"
                ]
            ]);
        }

        $id = request('id');
        $model = Code::findOrFail($id);
        if ($model->content != "") {
            $type = $model->type == 2? 'html':'php';
            $prompt .= "\n// 可基于当前代码进行生成【".$type."】代码：" . $model->content;
        }

        // 检查是否启用OpenAI API
        if (env('OPENAI_API_ENABLED', false)) {
            // 调用真实的OpenAI服务
            $generatedCode = $this->callOpenAIApi($prompt);
        } else {
            // 使用模拟数据
            $generatedCode = $this->mockAIService($prompt);
        }

        return response()->json([
            'code' => '9999',
            'message' => 'success',
            'data' => [
                'content' => $generatedCode
            ]
        ]);
    }

    /**
     * Mock AI service for demonstration
     * In real implementation, this would call an actual AI API
     */
    private function mockAIService($prompt)
    {
        // 根据不同的提示词返回不同的示例代码
        if (strpos($prompt, '待办') !== false || strpos($prompt, 'todo') !== false) {
            return <<<CODE
<!DOCTYPE html>
<html>
<head>
    <title>待办事项列表</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .todo-item { margin: 10px 0; padding: 10px; border: 1px solid #ddd; }
        .completed { text-decoration: line-through; color: #888; }
        input[type="text"] { padding: 8px; width: 300px; }
        button { padding: 8px 15px; margin: 5px; }
    </style>
</head>
<body>
    <h1>我的待办事项</h1>
    <div>
        <input type="text" id="todoInput" placeholder="添加新的待办事项">
        <button onclick="addTodo()">添加</button>
    </div>
    <ul id="todoList"></ul>

    <script>
        let todos = [];
        let id = 0;

        function addTodo() {
            const input = document.getElementById('todoInput');
            const text = input.value.trim();
            if (text) {
                todos.push({id: id++, text: text, completed: false});
                input.value = '';
                renderTodos();
            }
        }

        function toggleTodo(todoId) {
            todos = todos.map(todo => 
                todo.id === todoId ? {...todo, completed: !todo.completed} : todo
            );
            renderTodos();
        }

        function deleteTodo(todoId) {
            todos = todos.filter(todo => todo.id !== todoId);
            renderTodos();
        }

        function renderTodos() {
            const list = document.getElementById('todoList');
            list.innerHTML = '';
            todos.forEach(todo => {
                const item = document.createElement('li');
                item.className = 'todo-item ' + (todo.completed ? 'completed' : '');
                item.innerHTML = `
                    <input type="checkbox" ` + (todo.completed ? 'checked' : '') + ` 
                           onchange="toggleTodo(` + todo.id + `)">
                    ` + todo.text + `
                    <button onclick="deleteTodo(` + todo.id + `)">删除</button>
                `;
                list.appendChild(item);
            });
        }

        // 添加回车键支持
        document.getElementById('todoInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                addTodo();
            }
        });
    </script>
</body>
</html>
CODE;
        } else if (strpos($prompt, '函数') !== false || strpos($prompt, 'function') !== false) {
            return <<<CODE
/**
 * 计算两个数的和
 * @param {number} a - 第一个数
 * @param {number} b - 第二个数
 * @returns {number} 两数之和
 */
function add(a, b) {
    return a + b;
}

/**
 * 计算数组中所有元素的和
 * @param {number[]} numbers - 数字数组
 * @returns {number} 数组元素之和
 */
function sumArray(numbers) {
    return numbers.reduce((sum, num) => sum + num, 0);
}

// 使用示例
console.log(add(2, 3)); // 输出: 5
console.log(sumArray([1, 2, 3, 4, 5])); // 输出: 15
CODE;
        } else {
            // 默认示例代码
            return <<<CODE
// 这是AI根据您的需求生成的示例代码
function example() {
    console.log("Hello, World!");
    return true;
}

// 您可以在这里添加更多代码
const data = {
    message: "这是一个示例",
    timestamp: new Date().toISOString()
};

console.log(data);
CODE;
        }
    }

    /**
     * 调用OpenAI API生成代码
     */
    private function callOpenAIApi($prompt)
    {
        // 检查是否配置了OpenAI API密钥
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            return "// 错误：未配置OpenAI API密钥\n// 请在.env文件中设置OPENAI_API_KEY";
        }

        // 构造发送给OpenAI API的提示词
        $systemMessage = "你是一个专业的编程助手，专门帮助用户生成各种编程语言的代码。请根据用户的需求生成清晰、高效、有注释的代码。";
        $userMessage = "请生成实现以下功能的代码：\n\n" . $prompt . "\n\n请只返回代码，不要包含其他解释文字。";

        // 准备请求数据
        $data = [
            'model' => env('OPENAI_API_MODEL'),
            'messages' => [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user', 'content' => $userMessage]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ];

        // 使用cURL发送请求到OpenAI API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('OPENAI_API_URL'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 处理响应
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                return trim($result['choices'][0]['message']['content']);
            }
        }

        // 如果请求失败，返回错误信息
        return "// 代码生成失败，请稍后重试\n// 错误代码：" . $httpCode;
    }

    /**
     * 获取代码历史记录
     */
    public function getCodeHistory($id)
    {
        // 检查是否存在代码历史功能
        if (!class_exists(\App\Models\CodeHistory::class)) {
            return response()->json([
                'code' => '9999',
                'message' => 'success',
                'data' => [
                    'history' => [],
                    'current' => []
                ]
            ]);
        }

        // 获取当前代码
        $currentCode = Code::findOrFail($id);

        // 获取历史记录（按时间倒序排列）
        $history = \App\Models\CodeHistory::where('code_id', $id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'created_at', 'content']);

        return response()->json([
            'code' => '9999',
            'message' => 'success',
            'data' => [
                'history' => $history,
                'current' => [
                    'content' => $currentCode->content,
                    'updated_at' => $currentCode->updated_at
                ]
            ]
        ]);
    }

    /**
     * 获取历史版本代码内容
     */
    public function getHistoryCode($id)
    {
        // 检查是否存在代码历史功能
        if (!class_exists(\App\Models\CodeHistory::class)) {
            return response()->json([
                'code' => '9999',
                'message' => 'success',
                'data' => [
                    'content' => '// 无历史记录功能'
                ]
            ]);
        }

        $history = \App\Models\CodeHistory::findOrFail($id);

        return response()->json([
            'code' => '9999',
            'message' => 'success',
            'data' => [
                'content' => $history->content
            ]
        ]);
    }
}
