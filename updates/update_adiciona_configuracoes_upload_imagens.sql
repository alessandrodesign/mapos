INSERT INTO configuracoes (config)
VALUES ('codigo_servico'),
       ('codigo_servico_pref'),
       ('codigo_servico_ano'),
       ('codigo_servico_casa'),
       ('codigo_servico_resta'),
       ('logo_icone'),
       ('logo_favicon'),
       ('logo_default'),
       ('logo_black'),
       ('logo_black_full'),
       ('logo_white'),
       ('logo_white_full') ON DUPLICATE KEY
UPDATE config = config;

ALTER TABLE `servicos` ADD COLUMN `codigo` VARCHAR(45) NULL AFTER `idServicos`;
ALTER TABLE `servicos` CHANGE COLUMN `descricao` `descricao` TEXT NULL AFTER `nome`;
