<?xml version="1.0" encoding="UTF-8"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
  <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>{$merchant_identifier}</MerchantIdentifier>
  </Header>
    {strip}
        {""|indent:1}<MessageType>Product</MessageType>{""|indent:1:"\n"}
        {foreach from=$products item=product_set}

            {assign var="product" value=$product_set.product}
            {assign var="message" value=$product_set.message}
            {assign var="operation" value=$product_set.operation}
            {assign var="description_data" value=$product_set.description_data}
            {assign var="product_data" value=$product_set.product_data}

            {""|indent:1}<Message>{""|indent:1:"\n"}
            {""|indent:2}<MessageID>{$message}</MessageID>{""|indent:1:"\n"}
            {""|indent:2}<OperationType>{$operation}</OperationType>{""|indent:1:"\n"}
                {strip}
                {""|indent:2}<Product>{''|indent:1:"\n"}
                  {if is_array($product)}
                      {function name=genProduct}
                          {foreach from=$items key=tag item=item}
                              {if is_array($item) && !isset($item['Values']) && count($item)}
                                  {call name=genProduct items=$item level={$level}}
                              {else}
                                {textformat indent={$level}+1}<{$tag}>{$item}</{$tag}>{/textformat}{''|indent:1:"\n"}
                              {/if}
                          {/foreach}
                      {/function}
                      {call name=genProduct items=$product level=3}
                {/if}
                {if is_array($description_data) && count($description_data)}
                  {""|indent:3}<DescriptionData>{""|indent:1:"\n"}
                  {function name=genXml}
                      {foreach from=$items key=tag item=item}
                          {if is_array($item) && !isset($item['Values']) && count($item)}
                                {call name=genXml items=$item level={$level+1}}
                          {else}
                              {if is_array($item) && isset($item['Values']) && is_array($item['Values']) && count($item['Values'])}
                                  {foreach from=$item['Values'] key=idx item=item2}
                                  {textformat indent={$level}}
                                      <{$tag}{if isset($items[$tag][$idx]['Attributes']) && count($items[$tag][$idx]['Attributes'])}
                                            {foreach from=$items[$tag][$idx]['Attributes'] key=attribute_name item=attribute_value}
                                        {''|indent:1:" "}{$attribute_name}="{$attribute_value}"
                                    {/foreach}{/if}>{$item2}</{$tag}>{/textformat}{''|indent:1:"\n"}
                                  {/foreach}
                          {elseif is_array($item) && isset($item['Values'])}
                              {textformat indent={$level}}<{$tag}{if isset($items[$tag]['Attributes']) && count($items[$tag]['Attributes'])}
                              {foreach from=$items[$tag]['Attributes'] key=attribute_name item=attribute_value}
                                    {''|indent:1:" "}{$attribute_name}="{$attribute_value}"
                                {/foreach}
                              {/if}>{$item['Values']}</{$tag}>{/textformat}{''|indent:1:"\n"}
                              {/if}
                          {/if}
                      {/foreach}
                  {/function}
                  {call name=genXml items=$description_data level=4}
                  {""|indent:3}</DescriptionData>{""|indent:1:"\n"}
                {/if}

                {if is_array($product_data) && count($product_data)}
                    {""|indent:3}<ProductData>{''|indent:1:"\n"}
                    {call name=genXml items=$product_data level=4}
                    {""|indent:3}</ProductData>{''|indent:1:"\n"}
                {/if}

            {""|indent:2}</Product>{''|indent:1:"\n"}
            {""|indent:1}</Message>{''|indent:1:"\n"}
        {/foreach}
    {/strip}
</AmazonEnvelope>