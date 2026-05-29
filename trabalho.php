<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


$file = "tasks.json";

// cria o arquivo se não existir
if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
}

// lê tarefas
$tasks = json_decode(file_get_contents($file), true);

// ================= CRIAR TAREFA =================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["task"])) {

    $newTask = [
        "id" => uniqid(),
        "text" => htmlspecialchars($_POST["task"]),
        "time" => $_POST["time"],
        "created_at" => date("d/m/Y H:i")
    ];

    $tasks[] = $newTask;

    file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT));

    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}

// ================= DELETAR =================
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $tasks = array_filter($tasks, fn($t) => $t["id"] !== $id);

    file_put_contents($file, json_encode(array_values($tasks), JSON_PRETTY_PRINT));

    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>GERENCIADOR DE TAREFAS</title>

<style>
body {
    font-family: monospace, sans-serif;
    background: linear-gradient(135deg, #78acc8, #78acc8);
    margin: 0;
    color: #333;
}

.container {
    max-width: 700px;
    margin: 50px auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

h1 {
    text-align: center;
}

form {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

input {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

button {
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    background: #3b79cf;
    color: white;
    cursor: pointer;
}

button:hover {
    background: #3c7db6;
}

.task {
    display: flex;
    justify-content: space-between;
    padding: 12px;
    margin-top: 10px;
    background: #f7f7f7;
    border-radius: 8px;
}

.small {
    font-size: 12px;
    color: gray;
}

.delete {
    color: red;
    text-decoration: none;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="container">
    <h1>MY TO DO LIST</h1>

    <!-- FORMULÁRIO -->
    <form method="POST">
        <input type="text" name="task" placeholder="Digite uma tarefa..." required>
        <input type="datetime-local" name="time" required>
        <button type="submit">Adicionar</button>
    </form>

    <div id="tasks">
        <?php foreach ($tasks as $task): ?>
            <div class="task">
                <div>
                    <strong><?= $task["text"] ?></strong><br>
                    <span class="small">
                        Criado em: <?= $task["created_at"] ?><br>
                         <?= $task["time"] ?>
                    </span>
                </div>
                <a class="delete" href="?delete=<?= $task["id"] ?>">✖</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>

if (Notification.permission !== "granted") {
    Notification.requestPermission();
}


const tasks = <?php echo json_encode($tasks); ?>;


setInterval(() => {
    const now = new Date();

    tasks.forEach(task => {
        if (!task.time) return;

       const taskTime = new Date(task.time.replace("T", " "));

        if (
            now.getFullYear() === taskTime.getFullYear() &&
            now.getMonth() === taskTime.getMonth() &&
            now.getDate() === taskTime.getDate() &&
            now.getHours() === taskTime.getHours() &&
            now.getMinutes() === taskTime.getMinutes()
        ) {
            new Notification("Lembrete", {
                body: task.text
            });
        }
    });

}, 60000);


document.querySelectorAll(".delete").forEach(btn => {
    btn.addEventListener("click", e => {
        if (!confirm("Tem certeza que deseja deletar?")) {
            e.preventDefault();
        }
    });
   
});
</script>

</body>
</html>