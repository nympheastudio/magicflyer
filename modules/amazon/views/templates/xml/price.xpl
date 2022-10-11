<?xml version="1.0"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
  <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>A1MS0E6JGREIS3</MerchantIdentifier>
  </Header>
  {strip}
          <MessageType>Product</MessageType>{""|indent:1:"\n"}

          {foreach from=$products item=product}

              {""|indent:1}<Message>{""|indent:1:"\n"}
              {""|indent:2}<MessageID>{$product.message}</MessageID>{""|indent:1:"\n"}
              {""|indent:2}<OperationType>{$product.operation}</OperationType>{""|indent:1:"\n"}

              {""|indent:2}<Price>{''|indent:1:"\n"}
              {if is_array($product.product)}
                    {function name=genPrice}
                        {foreach from=$items key=tag item=item}
                            {if is_array($item) && !isset($item['Attributes']) && count($item)}
                                {call name=genPrice items=$item level={$level+1}}
                            {elseif is_array($item) && isset($item['Attributes'])}
                              {textformat indent={$level}}<{$tag}{foreach from=$item['Attributes'] key=attribute_name item=attribute_value}
                                  {''|indent:1:" "}{$attribute_name}="{$attribute_value}"{/foreach}>{$item['Value']}</{$tag}>{/textformat}{''|indent:1:"\n"}
                            {else}
                              {textformat indent={$level}}<{$tag}>{$item}</{$tag}>{/textformat}{''|indent:1:"\n"}
                            {/if}
                        {/foreach}
                    {/function}
                    {call name=genPrice items=$product.product level=3}
              {/if}
            {""|indent:2}</Price>{''|indent:1:"\n"}
            {""|indent:1}</Message>{''|indent:1:"\n"}
        {/foreach}
  {/strip}
</AmazonEnvelope>