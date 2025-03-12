//@ts-ignore
import classes from './SettingsButton.module.css'

export function SettingsButton(props: any) {
	return (
		<li onClick={props.onClick} className={classes.SettingsButton}>
			{props.title}
		</li>
	)
}

export default SettingsButton
