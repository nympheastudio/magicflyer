<?php

global $_MODULE;
$_MODULE = array();
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_d77d6e2864e75824429de650da524b91'] = 'FO Quantity Prices';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_20653a356a65af702a10e37b650c9515'] = 'Affiche les prix par quantité en Front pour les produits simple et les déclinaisons';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_00d23a76e43b46dae9ec7aa9dcbebb32'] = 'Actif';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_b9f5c797ebbf55adccdd8539a65a0241'] = 'Inactif';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_f4d1ea475eaa85102e2b4e6d95da84bd'] = 'Confirmation';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_c888438d14855d7d96a2724ee9c306bd'] = 'Mise à jour efectuée';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_de62775a71fc2bf7a13d7530ae24a7ed'] = 'Paramètres généraux';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_356f06a012553e7a0945768f766dfed2'] = 'Garder l\'affichage standard';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_0c6171eb8fb3c34290f9f1ebdd95bf4a'] = 'Garde l\'affiche standards des réductions par quantité (valable pour presta 1.4 uniquement)';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_c9cc8cce247e49bae79f15173ce97354'] = 'Sauvegarder';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_b4986e57c5a4e46da7986b684ed28325'] = 'Réduction nominales / Hook spécifique';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_0c3e7c5e60811c948a6a3f3f764e1a26'] = 'Cliquer pour voir';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_6fde560e2a973e42e309393194bdad0e'] = 'Si vous souhaitez supprimer l\'affichage nominal des prix dégressifs dans la page produit :';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_e2d2471688cdd530e365a734e6648dcd'] = '- Ouvrir le fichier product.tpl de votre thème';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_cba00d58b7d31f805534fb6ecd379ec2'] = '- Chercher la ligne (vers la ligne 450) :';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_401dc30471e8f3e84c5e395b5f9b9d61'] = '- La remplacer par :';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_f112119ee80047421ea17ddb88fb86cc'] = '- Juste avant cette ligne, vous pouvez ajouter l\'appel de hook suivant :';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_748eae29dbf1c2ac27f65c4635c0bc09'] = 'Correction bug du coeur';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_e736bf1170b30605a26c697d89b451bd'] = 'Jusqu\'à la version 1.5.3.1 incluse, un bug dans le coeur de Prestashop ne permet pas d\'afficher correctement les prix dégressifs.';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_4ad886f60ab899a6d10a2f6f05c05708'] = 'Il est reporté ici :';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_2d1b20ca6fd7645caa825f9de828bfb3'] = 'Pour le corriger :';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_46d1c379b32ee2d1c01a1e9bbd9cc9f4'] = '- Ouvrir le fichier /classes/Product.php';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_8ce3c6d9ab4e2a5999bb067a73e3e072'] = '- Chercher la ligne suivante, située dans la méthode priceCalculation, vers la ligne 2557:';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_9f9b5f12890761e4ff0eca61def37ca4'] = '- La remplacer par celles-ci, en complétant le dernière ligne';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_520c9b48bd8e00be18c32d77c32fa9c1'] = 'Correction du coeur No2';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_5cddd433bc7be841504ce24cac08f0ff'] = 'Suivant version de Prestashop, il se peut que les prix par quantité dans les tables de déclinaisons du Front-Office soient tous identiques.';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_d29c67731c6ccc5fe62e416edb7677d2'] = '- Ouvrez le fichier /classes/SpecificPrice.php';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_8768773a808e2c4c662a0c7fe2418aa3'] = '- Cherchez la ligne suivant dans la méthode getSpecificPrices() :';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_23053e318d2b15ce1370e7975fadaf56'] = '- Modifiez-la en remplaçant le $quantity par $real_quantity';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_b212d7015ad732261af1ae3a2fb61825'] = 'Ajout au panier Ajax dans les listings';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_c12cd9adbed8fccf75f4c39036e86fbe'] = 'Dans les pages de listes de produits en front office, propose l\'ajout au panier avec un popup ajax, permettant la sélection de déclinaison';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_3ef9bfa9bea248addb3d17610ada562a'] = 'Afficher prix de groupe';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_fae574ea5702e0657a15053aec051054'] = 'Affiche le prix barré du groupe du client, dans tous les tableaux de prix';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_5c2af36612171bf2cae920d97bd56ceb'] = 'Multiple de quantité minimale';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_da46a878459df4ac4ef1aee56a2513a1'] = 'Dans les tables de prix, gère la quantité comme multiple de quantité minimale';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_613866f90199a6924c0a2418c1f5d74f'] = 'Changer prix nominal';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_dc009e34cad66d7bb5d70591d32c56d3'] = 'Dans la page produit, change le prix nominal si un prix par quantité est applicable à la quantité voulue.';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_abd417a95473ec4844bba75d4de0de76'] = 'Afficher le meilleur prix';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_5463c8ab6e57360c262f25a635f3376f'] = 'Affiche le meilleur prix par quantité dans les pages listant les produits (ceux de la combinaison par défaut pour les produits à combinaisons)';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_ec6fbe21a5142b6905bd84124da47c0e'] = 'Garde l\'affiche standards des réductions par quantité';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_9eccff3e78441034ee6aca395514f17a'] = 'Paramètres Produits Simples';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_b8af52d56a1335ffdce13d15a305df77'] = 'Afficher prix produits';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_f8754aae7195d010460556e0bad099bd'] = 'Affiche les prix par quantités des produits simples';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_6771a4868900f5a019f356637a537fa5'] = 'Prix produits en onglet';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_d8cc46bb8f634dd8aa43adb0301b7cb6'] = 'Afficher les prix par quantité des produits simples dans un onglet de la fiche produit';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_6fc97d9b655cf1ca0284f285bea13471'] = 'Prix produits en pied de page';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_efaa92c42286dbb0765b17778df55aa9'] = 'Afficher les prix par quantité en pied de page de la page produit';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_9b66406f83fb38b64e8335d1015b6f08'] = 'Prix produit en hook spécifique';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_eaacb0f453d2f3b7c37e61428d8ac62c'] = 'Afficher les prix par quantité dans un hook spécifique';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_3fb1bd76da0a941e07ccbb1de9381b89'] = 'Pour l\'utiliser, ajouter le code suivant à l\'emplacement voulu dans le TPL de votre page produit';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_2d295d4d6be1c3286b6f9d154227382b'] = 'Prix produit en centre de page';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_e84d13bd1306352c799ec6775f177424'] = 'Afficher les prix par quantité comme tableau vertical dans la partie centrale de la fiche produit';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_4d00d1ebc4858c9ab0de91fcdf8e41b0'] = 'Afficher la colonne d\'ajout panier';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_7fd6f1e2153234feefb1a8c39cbdc84c'] = 'Afficher la colonne d\'ajout au panier dans la table des prix par quantité';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_73a496dca0808b75a522332a7c9cc2ad'] = 'Paramètres produits à Combinaisons';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_6648daa60346ed3cf37fbae7df2ed52c'] = 'Afficher prix déclinaisons';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_3c94f34971c02b2dda742ffed772ab39'] = 'Affiche les prix par quantité des déclianisons';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_cffb6e571df7d980b0d1f979f78688a8'] = 'Prix déclinaisons en onglet';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_951d3addbe79e3122e4c7e77e3c1cca8'] = 'Affiche les prix des déclianisons dans un onglet de la page produit';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_ac471e1683cba304f903bf18bcb5ca9d'] = 'Prix déclinaisons en pied de page';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_1bee7b398e4d0ce9ea4d824c6afd8950'] = 'Afficher les prix par quantité des déclinaison en pied de page de la page produit';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_b31c662164e12ed3c3b3860278c4cb49'] = 'Prix déclinaisons en hook spécifique';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_7163cb7ef2e22ca0749e7552d4925b7f'] = 'Afficher les prix par quantité des déclinaisons dans un hook spécifique';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_66e3fb99e9c0427191e57ee66d49e026'] = 'Prix déclinaisons en centre de page';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_810d7ee5ba7ace29eaea7aebb93ef520'] = 'Afficher les prix par quantité des déclinaisons comme tableau vertical dans la partie centrale de la fiche produit';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_b76da76bf70f8f8fa489abc95d33ed07'] = 'Seuls les prix de la déclinaisons par défaut sont affichés';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_a702920e18bfce2a142b97e0f5d9f9d5'] = 'Colonne image';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_37d250d6a88b88668dc615590607c58c'] = 'Affiche une colonne avec l\'image de la déclinaison';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_f18da57149060198032ae0b6ffd9ef4f'] = 'Afficher colonne qty=1';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_93615f0950950e008e73c702972f889b'] = 'Affiche systématiquement la colonne pour la quantité unitaire';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_ca02602412cdfadb5a09e7bfe74a4dd0'] = 'Afficher référence';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_2fde6f75b683e09a0cf609230ca280be'] = 'Afficher la colonne référence';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_f9989fdef8a14b1ffc35febb5c034dbc'] = 'Afficher la colonne EAN13';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_6ea8647c08029be6ab8533e82c1444b6'] = 'Afficher la colonne contenant l\'EAN13 de la déclinaison';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_90195d0f5a70500fe475d83b6e2970e6'] = 'Afficher la colonne d\'ajout panier';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_bf88b48624d4086138dd748f9d116f73'] = 'Afficher la colonne permettant d\'ajouter au panier';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_ca602d61cbaa3b3ca8658e8db529a4a5'] = 'Display HT / TTC/ Unitaire';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_857a512e60a6b85f9e2d19863b19ed4a'] = 'Affichage très spécifique qui affiche prix HT / TTC et unitaire';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_37f148029c3801e27de55e3db9a6c349'] = 'Exclusion de catégories';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_db0e4e02888d6165b56b46a5f2098689'] = 'Les tables de prix ne seront pas affichées pour les produits de ces catégories';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_b718adec73e04ce3ec720dd11a06a308'] = 'ID';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_49ee3087348e8d44e1feda1917443987'] = 'Nom';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_a82be0f551b8708bc08eb33cd9ded0cf'] = 'Information';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_b1c1d84a65180d5912b2dee38a48d6b5'] = 'Module version';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_db47d0e8a0007666d13c1a2b8ae79089'] = 'développé par';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_db5eb84117d06047c97c9a0191b5fffe'] = 'Suport';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_71fd5a6cebfbc8d60a3e19adf5295699'] = 'Nos modules';
$_MODULE['<{oleafoquantityprices}prestashop>oleafoquantityprices_bc9189406be84ec297464a514221406d'] = 'XXX';
$_MODULE['<{oleafoquantityprices}prestashop>install-1.7.7_885aaf4bec670aec54e86bb5a3715b20'] = '';
$_MODULE['<{oleafoquantityprices}prestashop>install-1.7.8_fd2a88b69ba71eb2db48d3423be95b26'] = '';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_b8af13ea9c8fe890c9979a1fa8dbde22'] = 'Référence';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_a3b6a4d0897451813950029a3066f219'] = 'EAN13';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_3601146c4e948c32b6424d2c0a7f0118'] = 'Prix';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_2771c223e032c086e6c2dee868a84b6b'] = 'Ajouter';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_2d0f6b8300be19cf35e89e66f0677f95'] = 'Ajouter au panier';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_cf3195d68353e406a85bceab813a0673'] = 'Prix par quantité d\'une déclinaison donnée';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_bc93c08a6e556af156c8def82843ab6e'] = 'Prix par quantité, toute déclinaison confondues';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_vertical_6420435c5b543acd4436d7db1505efbe'] = 'Quantité minimale';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_vertical_b5e772bba8a75738d596ba1d3ca9219c'] = 'Prix TTC';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_vertical_b00e1a94f0813522eaf4cfefde7cb05a'] = 'Prix HT';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_vertical_86141522ea2fa4cc1d702f3a0b050f1a'] = 'Prix unitaire HT';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_vertical_3601146c4e948c32b6424d2c0a7f0118'] = 'Prix';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_vertical_2771c223e032c086e6c2dee868a84b6b'] = 'Ajouter au panier';
$_MODULE['<{oleafoquantityprices}prestashop>prices_info_vertical_2d0f6b8300be19cf35e89e66f0677f95'] = 'Ajout au panier';
$_MODULE['<{oleafoquantityprices}prestashop>productbuttons_694e8d1f2ee056f98ee488bdc4982d73'] = 'Quantité';
$_MODULE['<{oleafoquantityprices}prestashop>productbuttons_53e5aa2c97fef1555d2511de8218c544'] = 'Par';
$_MODULE['<{oleafoquantityprices}prestashop>productbuttons_d98a07f84921b24ee30f86fd8cd85c3c'] = 'à partir de';
$_MODULE['<{oleafoquantityprices}prestashop>product_columnright_c9520cfb915d0abff2679f2e55dfd1fe'] = 'Prix par quantité';
$_MODULE['<{oleafoquantityprices}prestashop>product_columnright_694e8d1f2ee056f98ee488bdc4982d73'] = 'Quantité';
$_MODULE['<{oleafoquantityprices}prestashop>product_columnright_3601146c4e948c32b6424d2c0a7f0118'] = 'Prix';
$_MODULE['<{oleafoquantityprices}prestashop>product_footer_e16dd6e118732c5d1586d6aba0b62f3a'] = 'Prix';
$_MODULE['<{oleafoquantityprices}prestashop>product_footer_47f1a471a7f6a74053869cdb9cbf05bb'] = 'Prix par quantité';
$_MODULE['<{oleafoquantityprices}prestashop>product_price_block_5da618e8e4b89c66fe86e32cdafde142'] = 'A partir de ';
$_MODULE['<{oleafoquantityprices}prestashop>product_tab_content_e16dd6e118732c5d1586d6aba0b62f3a'] = 'Prix';
$_MODULE['<{oleafoquantityprices}prestashop>product_tab_e16dd6e118732c5d1586d6aba0b62f3a'] = 'Prix';
$_MODULE['<{oleafoquantityprices}prestashop>shopping_cart_53e5aa2c97fef1555d2511de8218c544'] = 'Par';
