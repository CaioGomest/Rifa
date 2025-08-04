<?php

if (session_status() == PHP_SESSION_NONE)
{
    session_start();
}
function salvarImagemPrincipal($input_name, $caminho_atual, $upload_dir)
{
    if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['error'] !== UPLOAD_ERR_OK)
        return $caminho_atual;

    $ext = strtolower(pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
        return $caminho_atual;

    $novo_nome = uniqid('img_') . '.' . $ext;
    if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $upload_dir . $novo_nome))
    {
        if (!empty($caminho_atual) && file_exists('../' . $caminho_atual))
            unlink('../' . $caminho_atual);

        return 'uploads/campanhas/' . $novo_nome;
    }

    return $caminho_atual;
}
function salvarGaleriaImagens($input_name, $galeria_atual, $upload_dir)
{
    if (!isset($_FILES[$input_name]) || !is_array($_FILES[$input_name]['tmp_name']))
        return implode(',', array_filter($galeria_atual));
    
    foreach ($_FILES[$input_name]['tmp_name'] as $key => $tmp_name)
    {
        if ($_FILES[$input_name]['error'][$key] === UPLOAD_ERR_OK)
        {
            $ext = strtolower(pathinfo($_FILES[$input_name]['name'][$key], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
            {
                $novo_nome = uniqid('gallery_') . '.' . $ext;
                if (move_uploaded_file($tmp_name, $upload_dir . $novo_nome))
                    $galeria_atual[] = 'uploads/campanhas/' . $novo_nome;
            }
        }
    }

    return implode(',', array_filter($galeria_atual));
}
function editarGaleriaImagens($input_name, $galeria_atual, $upload_dir)
{
    if (!isset($_FILES[$input_name]) || !is_array($_FILES[$input_name]['name']))
    {
        return is_array($galeria_atual) ? implode(',', array_filter($galeria_atual)) : $galeria_atual;
    }

    if (!is_array($galeria_atual))
    {
        $galeria_atual = $galeria_atual ? explode(',', $galeria_atual) : [];
    }

    foreach ($_FILES[$input_name]['tmp_name'] as $key => $tmp_name)
    {
        if ($_FILES[$input_name]['error'][$key] === UPLOAD_ERR_OK)
        {
            $ext = strtolower(pathinfo($_FILES[$input_name]['name'][$key], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
            {
                $novo_nome = uniqid('gallery_') . '.' . $ext;
                if (move_uploaded_file($tmp_name, $upload_dir . $novo_nome))
                {
                    $galeria_atual[] = 'uploads/campanhas/' . $novo_nome;
                }
            }
        }
    }

    return implode(',', array_filter($galeria_atual));
}
function editarImagemPrincipal($input_name, $caminho_atual, $upload_dir)
{
    if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['error'] === UPLOAD_ERR_NO_FILE)
    {
        return $caminho_atual;
    }

    if ($_FILES[$input_name]['error'] !== UPLOAD_ERR_OK)
    {
        return $caminho_atual;
    }

    $ext = strtolower(pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
    {
        return $caminho_atual;
    }

    if (!file_exists($upload_dir))
    {
        mkdir($upload_dir, 0777, true);
    }

    $novo_nome = uniqid('img_') . '.' . $ext;
    $caminho_completo = $upload_dir . $novo_nome;

    if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $caminho_completo))
    {
        if (!empty($caminho_atual))
        {
            $caminho_antigo = '../../' . $caminho_atual;
            if (file_exists($caminho_antigo))
            {
                unlink($caminho_antigo);
            }
        }
        return 'uploads/campanhas/' . $novo_nome;
    }

    return $caminho_atual;
}
function editarImagemCapa($input_name, $caminho_atual, $upload_dir)
{
    if (!isset($_POST[$input_name]) || $_POST[$input_name]['error'] === UPLOAD_ERR_NO_FILE)
    {
        return $caminho_atual;
    }

    if ($_POST[$input_name]['error'] !== UPLOAD_ERR_OK)
    {
        return $caminho_atual;
    }

    $ext = strtolower(pathinfo($_POST[$input_name]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
    {
        return $caminho_atual;
    }

    if (!file_exists($upload_dir))
    {
        mkdir($upload_dir, 0777, true);
    }

    $novo_nome = uniqid('img_') . '.' . $ext;
    $caminho_completo = $upload_dir . $novo_nome;

    if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $caminho_completo))
    {
        if (!empty($caminho_atual))
        {
            $caminho_antigo = '../../' . $caminho_atual;
            if (file_exists($caminho_antigo))
            {
                unlink($caminho_antigo);
            }
        }
        return 'uploads/campanhas/' . $novo_nome;
    }

    return $caminho_atual;
}
