<div class="yui-gc">
	<div class="yui-u first">
		<?php foreach ($newsEntries AS $entry) { ?>
		<div class="block">
			<div class="block-header"><?php $this->escape($entry['news_title']) ?></div>
			<div class="block-body">
			<?php echo $entry['news_body'] ?>
			</div>
			<div class="block-footer">
				Posted by <a href="<?php echo $this->url('user/view/' . $entry['news_author']) ?>"><?php $this->escape($entry['user_name']) ?></a> - <?php echo date('Y-m-d h:i:s A', $entry['news_time']) ?>
				<!-- todo: a fancy date function to handle timezones, today/yesterday, etc. -->
			</div>
		</div>
		<?php } ?>
		
		<div class="block">
			<div class="block-header">RPG Open</div>
			<div class="block-body">
			<p>Basic functionality, such as viewing news articles, and logging in,
			is now completed. You can't do a whole lot, but hey, <a href="#">here's a link</a>,
			and at least I've been doing <em>something</em>, eh?</p>
			</div>
			<div class="block-footer">
				Posted by <a href="<?php echo $this->url('user/view/1') ?>">Indigo</a> - Today, 7:08 PM
			</div>
		</div>
		<div class="block">
			<div class="block-header">Lorem Freaking Ipsum</div>
			<div class="block-body">
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum at quam nisl, eu fermentum nisi. Sed sit amet nisl nec justo ornare aliquet non eget turpis. Aliquam ullamcorper nisl ac leo ultricies sodales. Nulla porta, ligula vel tempus blandit, libero urna tempus diam, quis fringilla lectus risus luctus urna. Sed tempus magna magna. Nunc non blandit diam. Vivamus est libero, feugiat ut tincidunt commodo, ullamcorper sed metus. Mauris ligula urna, commodo quis vestibulum id, tempor a mi. Vivamus vehicula dolor sit amet orci auctor vitae consectetur eros venenatis. Etiam justo dui, tincidunt eget euismod tristique, blandit quis neque. Duis id dui non sapien accumsan congue.</p>

			<p>Vivamus aliquet, nisi eu hendrerit posuere, purus nisi posuere sapien, at ullamcorper eros turpis accumsan urna. Etiam et leo non lorem scelerisque euismod ac ut leo. Cras urna justo, adipiscing nec aliquam eu, vulputate eget nunc. Etiam vel ipsum nunc, sit amet sagittis odio. Aenean a eleifend massa. Donec luctus rhoncus suscipit. Aenean dolor libero, auctor in porta eget, lacinia vel metus. Mauris ultricies aliquet metus, eget interdum ligula fringilla eu. Phasellus suscipit mauris nec augue gravida ac mollis nulla laoreet. Quisque condimentum elementum nibh et placerat. Donec faucibus, sem id tincidunt suscipit, turpis ligula laoreet arcu, non ullamcorper justo nisl et velit. Donec pharetra semper mauris. Duis hendrerit euismod volutpat. Proin eget adipiscing mi. Sed semper facilisis iaculis. Vestibulum a odio mauris, ac sodales mauris. Etiam lacinia, libero in dapibus ultricies, nulla dolor lobortis nibh, eu aliquet est tellus eget sem.</p>
			</div>
			<div class="block-footer">
				Posted by <a href="<?php echo $this->url('user/view/1') ?>">Indigo</a> - Today, 7:08 PM
			</div>
		</div>
		<div class="block">
			<div class="block-header">Urgent: Lorem has Rabies</div>
			<div class="block-body">
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum at quam nisl, eu fermentum nisi. Sed sit amet nisl nec justo ornare aliquet non eget turpis. Aliquam ullamcorper nisl ac leo ultricies sodales. Nulla porta, ligula vel tempus blandit, libero urna tempus diam, quis fringilla lectus risus luctus urna. Sed tempus magna magna. Nunc non blandit diam. Vivamus est libero, feugiat ut tincidunt commodo, ullamcorper sed metus. Mauris ligula urna, commodo quis vestibulum id, tempor a mi. Vivamus vehicula dolor sit amet orci auctor vitae consectetur eros venenatis. Etiam justo dui, tincidunt eget euismod tristique, blandit quis neque. Duis id dui non sapien accumsan congue.</p>

			<p>Vivamus aliquet, nisi eu hendrerit posuere, purus nisi posuere sapien, at ullamcorper eros turpis accumsan urna. Etiam et leo non lorem scelerisque euismod ac ut leo. Cras urna justo, adipiscing nec aliquam eu, vulputate eget nunc. Etiam vel ipsum nunc, sit amet sagittis odio. Aenean a eleifend massa. Donec luctus rhoncus suscipit. Aenean dolor libero, auctor in porta eget, lacinia vel metus. Mauris ultricies aliquet metus, eget interdum ligula fringilla eu. Phasellus suscipit mauris nec augue gravida ac mollis nulla laoreet. Quisque condimentum elementum nibh et placerat. Donec faucibus, sem id tincidunt suscipit, turpis ligula laoreet arcu, non ullamcorper justo nisl et velit. Donec pharetra semper mauris. Duis hendrerit euismod volutpat. Proin eget adipiscing mi. Sed semper facilisis iaculis. Vestibulum a odio mauris, ac sodales mauris. Etiam lacinia, libero in dapibus ultricies, nulla dolor lobortis nibh, eu aliquet est tellus eget sem.</p>
			</div>
			<div class="block-footer">
				Posted by <a href="<?php echo $this->url('user/view/1') ?>">Indigo</a> - Today, 7:08 PM
			</div>
		</div>
	</div>
	<div class="yui-u">
		<div class="block-header">Quick Stats</div>
		<div class="block-body">
		Characters: <?php $this->escape($characters) ?> / <?php $this->escape($maxCharacters) ?><br />
		Squads: <?php $this->escape($squads) ?> / <?php $this->escape($maxSquads) ?><br />
		<?php $this->escape($moneyName) ?>: <?php $this->escape(number_format($money)) ?><br />
		Active Battles: <a href="<?php echo $this->url('battle/my') ?>"><?php $this->escape($activeBattles) ?></a>
		</div>
	</div>
</div>
