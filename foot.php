	</div>
</body>
</html>
<?php
if (isset($alert)) echo "<script>
setTimeout(\"$.Notify({caption: 'Alert!',content: '$alert',timeout: 6000,style: {background: 'red', color: 'white'}})\",250);
</script>";
else if (isset($warning)) echo "<script>
setTimeout(\"$.Notify({caption: 'Warning!',content: '$warning',timeout: 6000,style: {background: 'orange', color: 'white'}})\",250);
</script>";
else if (isset($success)) echo "<script>
setTimeout(\"$.Notify({caption: 'Success!',content: '$success',timeout: 5000,style: {background: 'green', color: 'white'}})\",250);
</script>";
?>