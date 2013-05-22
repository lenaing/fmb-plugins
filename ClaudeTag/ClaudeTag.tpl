    <li>
        <h2>Tags</h2>
        <div id="exttags">
        <ul>
{foreach from=$ext_tags_with_size item=tag}
{if isset($tag.cpt) and $tag.cpt == 1}
{assign var="nbTags" value="1 post"}
{else}
{assign var="nbTags" value=$tag.cpt|cat:" posts"}
{/if}
{if isset($extNiceURL) and $extNiceURL == t}
            <li><a href="tag-{$tag.id}-{$tag.title|niceurl}.html" title="{$nbTags}" style="font-size: {$tag.size}pt;" class="exttag">{$tag.title}</a></li>
{else}
            <li><a href="?tag={$tag.id}&amp;page=posts" title="{$nbTags}" style="font-size: {$tag.size}pt;" class="exttag">{$tag.title}</a></li>
{/if}
{/foreach}
        </ul>
        </div>
    </li>

