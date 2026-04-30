<?php
$file = "resources/views/dashboard/index.php";
$content = file_get_contents($file);

$parts = explode('<div class="row mt-4">', $content);
$top = $parts[0];
$bottom = '<div class="row mt-4">
        <!-- Dashboard Sol: Yaklaşan Randevular Tablosu (Genişlik 8 kolon) -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-clock-history me-1"></i> Yaklaşan Yer Gösterme Kayıtları</h6>
                    <a href="<?= htmlspecialchars(\'/emlak/public/viewing/index\') ?>" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tarih / Saat</th>
                                    <th>Müşteri</th>
                                    <th>İlgili İlan</th>
                                    <th>Durum</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($upcomingViewings)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Yaklaşan kayıtlı yer gösterme randevusu bulunmuyor.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($upcomingViewings as $viewing): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?= date(\'d.m.Y\', strtotime($viewing[\'viewing_date\'])) ?></div>
                                                <small class="text-muted"><?= date(\'H:i\', strtotime($viewing[\'viewing_date\'])) ?></small>
                                            </td>
                                            <td>
                                                <div><?= htmlspecialchars((string)$viewing[\'customer_name\']) ?></div>
                                                <small><a href="tel:<?= htmlspecialchars((string)$viewing[\'customer_phone\']) ?>" class="text-decoration-none"><i class="bi bi-telephone"></i> <?= htmlspecialchars((string)$viewing[\'customer_phone\']) ?></a></small>
                                            </td>
                                            <td>
                                                <div>İlan #<?= htmlspecialchars((string)$viewing[\'property_id\']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars((string)$viewing[\'district\']) ?> / <?= htmlspecialchars((string)$viewing[\'city\']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Bekliyor</span>
                                            </td>
                                            <td>
                                                <a href="/emlak/public/viewing/edit/<?= $viewing[\'id\'] ?>" class="btn btn-sm btn-light border" title="Detay / Güncelle">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Sağ: Basit Ajanda (To-Do) (Genişlik 4 kolon) -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100 border-warning" style="background-color: #fffdf5;">
                <div class="card-header py-3 bg-warning text-dark d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold"><i class="bi bi-journal-text me-1"></i> Günlük Ajanda / Yapılacaklar</h6>
                </div>
                <div class="card-body p-3">
                    <form id="taskForm" class="d-flex mb-3">
                        <input type="text" id="taskDesc" class="form-control form-control-sm me-2" placeholder="Yeni görev..." required>
                        <input type="date" id="taskDate" class="form-control form-control-sm me-2" value="<?= date(\'Y-m-d\') ?>" style="max-width:115px;" required>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Ekle</button>
                    </form>

                    <ul class="list-group list-group-flush" id="taskList" style="max-height: 400px; overflow-y: auto;">
                        <?php if(empty($tasks)): ?>
                            <li class="list-group-item bg-transparent text-muted text-center py-4" id="emptyTaskMsg">Henüz görev eklenmemiş.</li>
                        <?php else: ?>
                            <?php foreach($tasks as $t): ?>
                                <li class="list-group-item bg-transparent px-1 d-flex justify-content-between align-items-center">
                                    <div class="form-check w-100">
                                        <input class="form-check-input task-check" type="checkbox" value="<?= $t[\'id\'] ?>" id="task<?= $t[\'id\'] ?>" <?= $t[\'is_completed\'] ? \'checked\' : \'\' ?>>
                                        <label class="form-check-label w-100 <?= $t[\'is_completed\'] ? \'text-muted text-decoration-line-through\' : \'\' ?>" for="task<?= $t[\'id\'] ?>" style="cursor:pointer;" id="taskLabel<?= $t[\'id\'] ?>">
                                            <?= htmlspecialchars((string)$t[\'description\']) ?>
                                            <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-calendar-event"></i> <?= date(\'d.m.Y\', strtotime($t[\'task_date\'])) ?></div>
                                        </label>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const taskForm = document.getElementById("taskForm");
    const taskList = document.getElementById("taskList");
    const emptyMsg = document.getElementById("emptyTaskMsg");

    if (taskForm) {
        taskForm.addEventListener("submit", function(e) {
            e.preventDefault();
            const descInput = document.getElementById("taskDesc");
            const dateInput = document.getElementById("taskDate");

            const data = new FormData();
            data.append("description", descInput.value);
            data.append("task_date", dateInput.value);

            fetch("/emlak/public/dashboard/addTask", {
                method: "POST",
                body: data
            }).then(res => res.json()).then(res => {
                if (res.success) {
                    if (emptyMsg) emptyMsg.style.display = "none";
                    
                    const rawD = new Date(dateInput.value);
                    const dFormatted = String(rawD.getDate()).padStart(2,"0") + "." + String(rawD.getMonth()+1).padStart(2,"0") + "." + rawD.getFullYear();
                    
                    const li = document.createElement("li");
                    li.className = "list-group-item bg-transparent px-1 d-flex justify-content-between align-items-center";
                    li.innerHTML = `
                        <div class="form-check w-100">
                            <input class="form-check-input task-check" type="checkbox" value="${res.id}" id="task${res.id}">
                            <label class="form-check-label w-100" for="task${res.id}" style="cursor:pointer;" id="taskLabel${res.id}">
                                ${descInput.value}
                                <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-calendar-event"></i> ${dFormatted}</div>
                            </label>
                        </div>`;
                    taskList.prepend(li);
                    descInput.value = "";
                    
                    bindCheckbox(li.querySelector(".task-check"));
                } else {
                    alert(res.message || "Hata oluştu.");
                }
            }).catch(err => {
                console.error(err);
            });
        });
    }

    function bindCheckbox(chk) {
        chk.addEventListener("change", function() {
            const taskId = this.value;
            const isCompleted = this.checked;
            const label = document.getElementById("taskLabel" + taskId);

            if (isCompleted) {
                label.classList.add("text-muted", "text-decoration-line-through");
            } else {
                label.classList.remove("text-muted", "text-decoration-line-through");
            }

            const fd = new FormData();
            fd.append("id", taskId);
            fd.append("is_completed", isCompleted);

            fetch("/emlak/public/dashboard/completeTask", {
                method: "POST",
                body: fd
            });
        });
    }

    document.querySelectorAll(".task-check").forEach(bindCheckbox);
});
</script>
';
file_put_contents($file, $top . $bottom);
echo "Dashboard HTML overwritten manually.\n";