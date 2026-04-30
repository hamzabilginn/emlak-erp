<?php
$file = "resources/views/properties/create.php";
$content = file_get_contents($file);

$searchOption = '                          <option value="Var">Evet, Eşyalı</option>
                      </select>
                  </div>';

$replaceOption = '                          <option value="Var">Evet, Eşyalı</option>
                      </select>
                  </div>
                  
                  <div class="col-md-4 mb-3">
                      <label class="form-label">Asansör</label>
                      <select name="details[elevator]" class="form-select">
                          <option value="">Seçiniz / Boş Bırak</option>
                          <option value="Var">Evet, Asansörlü</option>
                          <option value="Yok">Hayır, Asansör Yok</option>
                      </select>
                  </div>';

$content = str_replace($searchOption, $replaceOption, $content);
file_put_contents($file, $content);
echo "Added Elevator field to create.php\n";